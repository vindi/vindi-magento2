<?php

namespace Vindi\Payment\Model\Payment;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod as OriginAbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;
use Psr\Log\LoggerInterface;
use Vindi\Payment\Api\PlanManagementInterface;
use Vindi\Payment\Api\ProductManagementInterface;
use Vindi\Payment\Api\SubscriptionInterface;
use Vindi\Payment\Helper\Api;
use Vindi\Payment\Model\PaymentProfile;
use Vindi\Payment\Model\PaymentProfileFactory;
use Vindi\Payment\Model\PaymentProfileRepository;
use Magento\Framework\App\ResourceConnection;
use Vindi\Payment\Model\VindiPlanRepository;

/**
 * Class AbstractMethod
 *
 * @package \Vindi\Payment\Model\Payment
 */
abstract class AbstractMethod extends OriginAbstractMethod
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var Bill
     */
    protected $bill;

    /**
     * @var Profile
     */
    protected $profile;

    /**
     * @var PaymentMethod
     */
    protected $paymentMethod;

    /**
     * @var LoggerInterface
     */
    protected $psrLogger;

    /**
     * @var TimezoneInterface
     */
    protected $date;

    /**
     * @var ProductManagementInterface
     */
    private $productManagement;

    /**
     * @var \Vindi\Payment\Helper\Data
     */
    private $helperData;

    /**
     * @var PlanManagementInterface
     */
    private $planManagement;

    /**
     * @var SubscriptionInterface
     */
    private $subscriptionRepository;

    /**
     * @var PaymentProfileFactory
     */
    private $paymentProfileFactory;

    /**
     * @var PaymentProfileRepository
     */
    private $paymentProfileRepository;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var VindiPlanRepository
     */
    protected $vindiPlanRepository;

    /**
     * AbstractMethod constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param Api $api
     * @param InvoiceService $invoiceService
     * @param Customer $customer
     * @param ProductManagementInterface $productManagement
     * @param PlanManagementInterface $planManagement
     * @param SubscriptionInterface $subscriptionRepository
     * @param PaymentProfileFactory $paymentProfileFactory
     * @param PaymentProfileRepository $paymentProfileRepository
     * @param ResourceConnection $resourceConnection
     * @param Bill $bill
     * @param Profile $profile
     * @param PaymentMethod $paymentMethod
     * @param LoggerInterface $psrLogger
     * @param TimezoneInterface $date
     * @param \Vindi\Payment\Helper\Data $helperData
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param VindiPlanRepository $vindiPlanRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        Api $api,
        InvoiceService $invoiceService,
        Customer $customer,
        ProductManagementInterface $productManagement,
        PlanManagementInterface $planManagement,
        SubscriptionInterface $subscriptionRepository,
        VindiPlanRepository $vindiPlanRepository,
        PaymentProfileFactory $paymentProfileFactory,
        PaymentProfileRepository $paymentProfileRepository,
        ResourceConnection $resourceConnection,
        Bill $bill,
        Profile $profile,
        PaymentMethod $paymentMethod,
        LoggerInterface $psrLogger,
        TimezoneInterface $date,
        \Vindi\Payment\Helper\Data $helperData,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
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
        $this->invoiceService = $invoiceService;
        $this->customer = $customer;
        $this->bill = $bill;
        $this->profile = $profile;
        $this->paymentMethod = $paymentMethod;
        $this->psrLogger = $psrLogger;
        $this->date = $date;
        $this->productManagement = $productManagement;
        $this->helperData = $helperData;
        $this->planManagement = $planManagement;
        $this->vindiPlanRepository = $vindiPlanRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->paymentProfileFactory = $paymentProfileFactory;
        $this->paymentProfileRepository = $paymentProfileRepository;
        $this->resourceConnection = $resourceConnection;
        $this->connection = $this->resourceConnection->getConnection();
    }

    /**
     * @return string
     */
    abstract protected function getPaymentMethodCode();

    /**
     * Check whether payment method can be used
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (
            $this->getPaymentMethodCode() == PaymentMethod::BANK_SLIP
            || $this->getPaymentMethodCode() == PaymentMethod::BANK_SLIP_PIX
            || $this->getPaymentMethodCode() == PaymentMethod::PIX
        ) {
            foreach ($quote->getItems() as $item) {
                if ($this->helperData->isVindiPlan($item->getProductId())) {
                    $product = $this->helperData->getProductById($item->getProductId());
                    if (
                        $product->getData('vindi_billing_trigger_day') > 0 ||
                        $product->getData('vindi_billing_trigger_type') == 'end_of_period'
                    ) {
                        return false;
                    }
                }
            }
        }
        return parent::isAvailable($quote);
    }

    /**
     * Assign data to info model instance
     *
     * @param mixed $data
     *
     * @return $this
     * @throws LocalizedException
     */
    public function assignData(DataObject $data)
    {
        parent::assignData($data);
        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws LocalizedException
     */
    public function validate()
    {
        parent::validate();
        return $this;
    }

    /**
     * Authorize payment abstract method
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
     * @throws LocalizedException
     * @return $this|string
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        parent::authorize($payment, $amount);
        return $this->processPayment($payment, $amount);
    }

    /**
     * Capture payment abstract method
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
     * @throws LocalizedException
     * @return $this|string
     */
    public function capture(InfoInterface $payment, $amount)
    {
        parent::capture($payment, $amount);
        return $this->processPayment($payment, $amount);
    }

    /**
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
     *
     * @throws LocalizedException
     * @return $this|string
     */
    protected function processPayment(InfoInterface $payment, $amount)
    {
        /** @var Order $order */
        $order = $payment->getOrder();

        if ($plan = $this->isSubscriptionOrder($order)) {
            return $this->handleSubscriptionOrder($payment, $plan);
        }

        $customerId = $this->customer->findOrCreate($order);

        $productList = $this->productManagement->findOrCreateProductsFromOrder($order);

        $body = [
            'customer_id' => $customerId,
            'payment_method_code' => $this->getPaymentMethodCode(),
            'bill_items' => $productList,
            'code' => $order->getIncrementId()
        ];

        if ($body['payment_method_code'] === PaymentMethod::CREDIT_CARD) {
            $paymentProfile = $this->createPaymentProfile($order, $payment, $customerId);
            $body['payment_profile'] = ['id' => $paymentProfile->getData('payment_profile_id')];
        }

        if ($installments = $payment->getAdditionalInformation('installments')) {
            $body['installments'] = (int)$installments;
        }

        if ($bill = $this->bill->create($body)) {
            $this->handleBankSplitAdditionalInformation($payment, $body, $bill);
            if ($this->successfullyPaid($body, $bill)) {
                $this->handleBankSplitAdditionalInformation($payment, $body, $bill);
                $order->setVindiBillId($bill['id']);
                return $bill['id'];
            }
            $this->bill->delete($bill['id']);
        }

        return $this->handleError($order);
    }

    /**
     * @param InfoInterface $payment
     * @param OrderItemInterface $orderItem
     * @return mixed
     * @throws LocalizedException
     */
    private function handleSubscriptionOrder(InfoInterface $payment, OrderItemInterface $orderItem)
    {
        /** @var Order $order */
        $order = $payment->getOrder();
        $customerId = $this->customer->findOrCreate($order);

        $options = $orderItem->getProductOptions();
        if (!empty($options['info_buyRequest']['selected_plan_id'])) {
            $planId    = $options['info_buyRequest']['selected_plan_id'];
            $vindiPlan = $this->vindiPlanRepository->getById($planId);
            $planId    = $vindiPlan->getVindiId();
        } else {
            $planId = $this->planManagement->create($orderItem->getProductId());
        }

        $productItems = $this->productManagement->findOrCreateProductsToSubscription($order);

        $body = [
            'customer_id' => $customerId,
            'payment_method_code' => $this->getPaymentMethodCode(),
            'plan_id' => $planId,
            'product_items' => $productItems,
            'code' => $order->getIncrementId()
        ];

        if ($body['payment_method_code'] === PaymentMethod::CREDIT_CARD) {
            $paymentProfile = $this->createPaymentProfile($order, $payment, $customerId);
            $body['payment_profile'] = ['id' => $paymentProfile->getData('payment_profile_id')];
        }

        if ($installments = $payment->getAdditionalInformation('installments')) {
            $body['installments'] = (int) $installments;
        }

        if ($responseData = $this->subscriptionRepository->create($body)) {
            $bill = $responseData['bill'];
            $subscription = $responseData['subscription'];

            if ($subscription) {
                $this->saveSubscriptionToDatabase($subscription, $order);
            }

            if ($bill) {
                $this->handleBankSplitAdditionalInformation($payment, $body, $bill);
            }

            if ($this->successfullyPaid($body, $bill, $subscription)) {
                if ($bill) {
                    $this->handleBankSplitAdditionalInformation($payment, $body, $bill);
                }
                $billId = $bill['id'] ?? 0;
                $order->setVindiBillId($billId);
                $order->setVindiSubscriptionId($responseData['subscription']['id']);
                return $billId;
            }

            $this->subscriptionRepository->deleteAndCancelBills($subscription['id']);
        }

        return $this->handleError($order);
    }

    /**
     * @param array $subscription
     * @param Order $order
     * @return void
     * @throws \Exception
     */
    private function saveSubscriptionToDatabase(array $subscription, Order $order)
    {
        $tableName = $this->resourceConnection->getTableName('vindi_subscription');
        $startAt = new \DateTime($subscription['start_at']);

        $data = [
            'id'              => $subscription['id'],
            'client'          => $subscription['customer']['name'],
            'customer_email'  => $subscription['customer']['email'],
            'customer_id'     => $order->getCustomerId(),
            'plan'            => $subscription['plan']['name'],
            'payment_method'  => $subscription['payment_method']['code'],
            'payment_profile' => $subscription['payment_profile']['id'] ?? null,
            'status'          => $subscription['status'],
            'start_at'        => $startAt->format('Y-m-d H:i:s')
        ];

        try {
            $this->connection->insert($tableName, $data);
        } catch (\Exception $e) {
            $this->psrLogger->error('Error saving subscription to database: ' . $e->getMessage());
        }
    }

    /**
     * Check whether the order is a subscription, based on the product or product purchase options.
     *
     * @param Order $order
     * @return OrderItemInterface|bool
     */
    private function isSubscriptionOrder(Order $order)
    {
        foreach ($order->getItems() as $item) {
            try {
                if ($this->helperData->isVindiPlan($item->getProductId())) {
                    return $item;
                }

                $options = $item->getProductOptions();
                if (!empty($options['info_buyRequest']['selected_plan_id'])) {
                    return $item;
                }
            } catch (NoSuchEntityException $e) {
            }
        }

        return false;
    }


    /**
     * @param Order $order
     * @throws LocalizedException
     */
    private function handleError(Order $order)
    {
        $this->psrLogger->error(__(sprintf('Error on order payment %d.', $order->getId())));
        $message = __('There has been a payment confirmation error. Verify data and try again');
        $order->setState(Order::STATE_CANCELED)
            ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CANCELED))
            ->addStatusHistoryComment($message->getText());

        throw new LocalizedException($message);
    }

    /**
     * @param InfoInterface $payment
     * @param array $body
     * @param $bill
     */
    protected function handleBankSplitAdditionalInformation(InfoInterface $payment, array $body, $bill)
    {
        if (
            $body['payment_method_code'] === PaymentMethod::BANK_SLIP
            || $body['payment_method_code'] === PaymentMethod::BANK_SLIP_PIX
        ) {
            $payment->setAdditionalInformation('print_url', $bill['charges'][0]['print_url']);
            $payment->setAdditionalInformation('due_at', $bill['charges'][0]['due_at']);
        }

        $isValidPix = isset($bill['charges'][0]['last_transaction']['gateway_response_fields']['qrcode_original_path']);
        if (
            $isValidPix
            && (
                $body['payment_method_code'] === PaymentMethod::PIX
                || $body['payment_method_code'] === PaymentMethod::BANK_SLIP_PIX
            )
        ) {
            foreach ($bill['charges'][0]['last_transaction']['gateway_response_fields'] as $key => $value) {
                $payment->setAdditionalInformation($key, $value);
            }
        }
    }

    /**
     * @param array $body
     * @param $bill
     * @param array $subscription
     * @return bool
     */
    private function successfullyPaid(array $body, $bill, array $subscription = [])
    {
        // nova validação para permitir pedidos com pagamento/fatura pendente
        if (!$bill) {
            $billingType = $subscription['billing_trigger_type'] ?? null;
            if ($billingType != 'day_of_month') {
                return true;
            }
        }

        return $this->isValidPaymentMethodCode($body['payment_method_code'])
            || $this->isValidStatus($bill)
            || $this->isWaitingPaymentMethodResponse($bill);
    }

    /**
     * @param $paymentMethodCode
     *
     * @return bool
     */
    protected function isValidPaymentMethodCode($paymentMethodCode)
    {
        $paymentMethodsCode = [
            PaymentMethod::BANK_SLIP,
            PaymentMethod::DEBIT_CARD
        ];

        return in_array($paymentMethodCode, $paymentMethodsCode);
    }

    /**
     * @param $bill
     *
     * @return bool
     */
    protected function isWaitingPaymentMethodResponse($bill)
    {
        if (!$bill) {
            return false;
        }

        return reset($bill['charges'])['last_transaction']['status'] === Bill::WAITING_STATUS;
    }

    /**
     * @param $bill
     *
     * @return bool
     */
    protected function isValidStatus($bill)
    {
        if (!$bill) {
            return false;
        }

        $billStatus = [
            Bill::PAID_STATUS,
            Bill::REVIEW_STATUS
        ];

        $chargeStatus = reset($bill['charges'])['status'] === Bill::FRAUD_REVIEW_STATUS;

        return in_array($bill['status'], $billStatus) || $chargeStatus;
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function createPaymentProfile(Order $order, InfoInterface $payment, $customerId)
    {
        $paymentProfile = $this->profile->create($payment, $customerId, $this->getPaymentMethodCode());
        $paymentProfileData = $paymentProfile['payment_profile'];

        $paymentProfileModel = $this->paymentProfileFactory->create();
        $paymentProfileModel->setData([
            'payment_profile_id' => $paymentProfileData['id'],
            'vindi_customer_id' => $customerId,
            'customer_id' => $order->getCustomerId(),
            'customer_email' => $order->getCustomerEmail(),
            'cc_name' => $payment->getCcOwner(),
            'cc_type' => $payment->getCcType(),
            'cc_last_4' => $payment->getCcLast4(),
            'status' => $paymentProfileData["status"],
            'token' => $paymentProfileData["token"],
            'type' => $paymentProfileData["type"],
        ]);

        $this->paymentProfileRepository->save($paymentProfileModel);

        return $paymentProfileModel;
    }
}
