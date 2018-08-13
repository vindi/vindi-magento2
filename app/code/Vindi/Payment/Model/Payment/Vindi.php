<?php


namespace Vindi\Payment\Model\Payment;

use Magento\Framework\DataObject;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Model\Order;
use Vindi\Payment\Block\Info\Cc;
use Vindi\Payment\Model\Api;
use Magento\Directory\Helper\Data as DirectoryHelper;

class Vindi extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = "vindi";
    protected $_isOffline = true;
    protected $_infoBlockType = Cc::class;

    /**
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial = false;

    /**
     * @var bool
     */
    protected $_canRefund = false;

    /**
     * @var bool
     */
    protected $_canVoid = false;

    /**
     * @var bool
     */
    protected $_canUseInternal = true;

    /**
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * @var bool
     */
    protected $_canUseForMultishipping = false;

    /**
     * @var bool
     */
    protected $_isInitializeNeeded = false;

    /**
     * @var bool
     */
    protected $_canSaveCc = false;

    protected $_invoiceService;
    protected $api, $order;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Vindi\Payment\Model\Payment\Api $api,
        Customer $customer,
        Product $product,
        Bill $bill,
        Profile $profile,
        \Psr\Log\LoggerInterface $psrLogger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        PaymentMethod $paymentMethod,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_logger = $logger;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->api = $api;
        $this->_invoiceService = $invoiceService;
        $this->customer = $customer;
        $this->product = $product;
        $this->bill = $bill;
        $this->paymentMethod = $paymentMethod;
        $this->date = $date;
        $this->psrLogger = $psrLogger;
        $this->profile = $profile;
    }


    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    )
    {
        return parent::isAvailable($quote);
    }

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     *
     * @return  VindiCreditcard
     */
    public function assignData(DataObject $data)
    {
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_object($additionalData)) {
            $additionalData = new DataObject($additionalData ?: []);
        }

        $info = $this->getInfoInstance();

        $info->setAdditionalInformation('installments', $additionalData->getCcInstallments());

        $info->addData(
            [
                'cc_type' => $additionalData->getCcType(),
                'cc_owner' => $additionalData->getCcOwner(),
                'cc_last_4' => substr($additionalData->getCcNumber(), -4),
                'cc_number' => $additionalData->getCcNumber(),
                'cc_cid' => $additionalData->getCcCvv(),
                'cc_exp_month' => $additionalData->getCcExpMonth(),
                'cc_exp_year' => $additionalData->getCcExpYear(),
                'cc_ss_issue' => $additionalData->getCcSsIssue(),
                'cc_ss_start_month' => $additionalData->getCcSsStartMonth(),
                'cc_ss_start_year' => $additionalData->getCcSsStartYear()
            ]
        );
        $info->save();

        return $this;
    }

    /**
     * @return string
     */
    protected function getPaymentMethodCode()
    {
        return 'credit_card';
    }

    public function validate()
    {
        $info = $this->getInfoInstance();
        $ccNumber = $info->getCcNumber();
        // remove credit card non-numbers
        $ccNumber = preg_replace('/\D/', '', $ccNumber);

        $info->setCcNumber($ccNumber);

        if (!$this->paymentMethod->isCcTypeValid($info->getCcType())) {
            return $this->addError(__('Credit card type is not allowed for this payment method.'));
        }

        return $this;
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        $customerId = $this->customer->findOrCreate($order);
        $paymentProfile = $this->profile->create($payment, $customerId, $this->getPaymentMethodCode());
        $productList = $this->product->findOrCreateProducts($order);

        $body = [
            'customer_id' => $customerId,
            'payment_method_code' => $this->getPaymentMethodCode(),
            'bill_items' => $productList,
            'payment_profile' => ['id' => $paymentProfile['payment_profile']['id']]
        ];

        if ($installments = $payment->getAdditionalInformation('installments')) {
            $body['installments'] = (int)$installments;
        }

        if ($bill = $this->bill->create($body)) {
            if (
                $bill['code'] === PaymentMethod::BANK_SLIP
                || $bill['code'] === PaymentMethod::DEBIT_CARD
                || $bill['status'] === Bill::PAID_STATUS
                || $bill['status'] === Bill::REVIEW_STATUS
            ) {
                $order->setVindiBillId($bill['id']);
                $order->save();
                return $bill['id'];
            }
            $this->bill->delete($bill['id']);
        }

        $this->psrLogger->error(__(sprintf('Error on order payment %d.', $order->getId())));
        $message = __('There has been a payment confirmation error. Verify data and try again')->getText();
        $payment->setStatus(
            Order::STATE_CANCELED,
            Order::STATE_CANCELED,
            $message,
            true
        );
        throw new \Exception($message);
    }
}
