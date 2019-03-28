<?php

namespace Vindi\Payment\Model\Payment;

use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;
use Vindi\Payment\Block\Info\BankSlip as InfoBlock;

class BankSlip extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'vindi_bankslip';
    protected $_isOffline = true;
    protected $_infoBlockType = InfoBlock::class;

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
    protected $api;
    protected $order;

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
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

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
    ) {

        return parent::isAvailable($quote);
    }

    /**
     * Assign data to info model instance
     *
     * @param mixed $data
     *
     * @return BankSlip
     */
    public function assignData(DataObject $data)
    {
        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('installments', 1);
        $info->save();

        return $this;
    }

    /**
     * @return string
     */
    protected function getPaymentMethodCode()
    {
        return PaymentMethod::BANK_SLIP;
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        $customerId = $this->customer->findOrCreate($order);
        $productList = $this->product->findOrCreateProducts($order);

        $body = [
            'customer_id' => $customerId,
            'payment_method_code' => $this->getPaymentMethodCode(),
            'bill_items' => $productList
        ];

        if ($installments = $payment->getAdditionalInformation('installments')) {
            $body['installments'] = (int)$installments;
        }

        if ($bill = $this->bill->create($body)) {
            if ($bill['charges'][0]['payment_method']['code'] === PaymentMethod::BANK_SLIP) {
                $payment->setAdditionalInformation('print_url', $bill['charges'][0]['print_url']);
                $payment->setAdditionalInformation('due_at', $bill['charges'][0]['due_at']);
            }

            if ($bill['charges'][0]['payment_method']['code'] === PaymentMethod::BANK_SLIP
                || $bill['charges'][0]['payment_method']['code'] === PaymentMethod::DEBIT_CARD
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
        throw new \Magento\Framework\Exception\LocalizedException($message);
    }
}
