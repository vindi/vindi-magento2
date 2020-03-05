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
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;
use Psr\Log\LoggerInterface;
use Vindi\Payment\Api\PlanManagementInterface;
use Vindi\Payment\Api\ProductManagementInterface;
use Vindi\Payment\Api\SubscriptionInterface;

abstract class AbstractMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * @var \Vindi\Payment\Helper\Api
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

    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        \Vindi\Payment\Helper\Api $api,
        InvoiceService $invoiceService,
        Customer $customer,
        ProductManagementInterface $productManagement,
        PlanManagementInterface $planManagement,
        SubscriptionInterface $subscriptionRepository,
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
        $this->subscriptionRepository = $subscriptionRepository;
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
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
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
        $this->processPayment($payment, $amount);
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
        $this->processPayment($payment, $amount);
    }

    /**
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
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
            'bill_items' => $productList
        ];

        if ($body['payment_method_code'] === PaymentMethod::CREDIT_CARD) {
            $paymentProfile = $this->profile->create($payment, $customerId, $this->getPaymentMethodCode());
            $body['payment_profile'] = ['id' => $paymentProfile['payment_profile']['id']];
        }

        if ($installments = $payment->getAdditionalInformation('installments')) {
            $body['installments'] = (int)$installments;
        }

        if ($bill = $this->bill->create($body)) {
            $this->handleBankSplitAdditionalInformation($payment, $body, $bill);
            if ($this->successfullyPaid($body, $bill)) {
                $order->setVindiBillId($bill['id']);
                return $bill['id'];
            }
            $this->bill->delete($bill['id']);
        }

        return $this->handleError($order);
    }

    /**
     * @param InfoInterface $payment
     * @param OrderItemInterface $plan
     * @return mixed
     * @throws LocalizedException
     */
    private function handleSubscriptionOrder(InfoInterface $payment, OrderItemInterface $plan)
    {
        /** @var Order $order */
        $order = $payment->getOrder();
        $customerId = $this->customer->findOrCreate($order);
        $planId = $this->planManagement->create($plan->getProductId());

        $productItems = $this->productManagement->findOrCreateProductsToSubscription($order);

        $body = [
            'customer_id' => $customerId,
            'payment_method_code' => $this->getPaymentMethodCode(),
            'plan_id' => $planId,
            'product_items' => $productItems,
            'code' => $order->getIncrementId()
        ];

        if ($body['payment_method_code'] === PaymentMethod::CREDIT_CARD) {
            $paymentProfile = $this->profile->create($payment, $customerId, $this->getPaymentMethodCode());
            $body['payment_profile'] = ['id' => $paymentProfile['payment_profile']['id']];
        }

        if ($installments = $payment->getAdditionalInformation('installments')) {
            $body['installments'] = (int)$installments;
        }

        if ($responseData = $this->subscriptionRepository->create($body)) {
            $bill = $responseData['bill'];
            $this->handleBankSplitAdditionalInformation($payment, $body, $bill);
            if ($this->successfullyPaid($body, $bill)) {
                $order->setVindiBillId($bill['id']);
                return $bill['id'];
            }
            $this->bill->delete($bill['id']);
        }

        return $this->handleError($order);
    }

    /**
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
        if ($body['payment_method_code'] === PaymentMethod::BANK_SLIP) {
            $payment->setAdditionalInformation('print_url', $bill['charges'][0]['print_url']);
            $payment->setAdditionalInformation('due_at', $bill['charges'][0]['due_at']);
        }
    }

    /**
     * @param array $body
     * @param $bill
     * @return bool
     */
    private function successfullyPaid(array $body, $bill)
    {
        if (
            $body['payment_method_code'] === PaymentMethod::BANK_SLIP
            || $body['payment_method_code'] === PaymentMethod::DEBIT_CARD
            || $bill['status'] === Bill::PAID_STATUS
            || $bill['status'] === Bill::REVIEW_STATUS
            || reset($bill['charges'])['status'] === Bill::FRAUD_REVIEW_STATUS
        ) {
            return true;
        }

        return false;
    }
}
