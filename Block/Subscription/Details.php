<?php
namespace Vindi\Payment\Block\Subscription;

use DateTime;
use Exception;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Vindi\Payment\Model\ResourceModel\Subscription\CollectionFactory as SubscriptionCollectionFactory;
use Vindi\Payment\Model\ResourceModel\PaymentProfile\CollectionFactory as PaymentProfileCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Vindi\Payment\Helper\Api;
use Vindi\Payment\Model\ResourceModel\SubscriptionOrder\CollectionFactory as SubscriptionOrderCollectionFactory;
use Magento\Framework\Registry;
use Vindi\Payment\Model\Config\Source\OrderStatus;

/**
 * Class Details
 * @package Vindi\Payment\Block\Subscription
 */
class Details extends Template
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var SubscriptionCollectionFactory
     */
    protected $subscriptionCollectionFactory;

    /**
     * @var PaymentProfileCollectionFactory
     */
    protected $paymentProfileCollectionFactory;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var Api
     */
    private $api;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceHelper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var SubscriptionOrderCollectionFactory
     */
    private $subscriptionsOrderCollectionFactory;

    /**
     * @var array
     */
    private $subscriptionData = null;

    /**
     * @var array
     */
    private $periods = null;

    /**
     * @var OrderStatus
     */
    private $orderStatus;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param SubscriptionCollectionFactory $subscriptionCollectionFactory
     * @param PaymentProfileCollectionFactory $paymentProfileCollectionFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param Api $api
     * @param PriceCurrencyInterface $priceHelper
     * @param Registry $registry
     * @param SubscriptionOrderCollectionFactory $subscriptionsOrderCollectionFactory
     * @param OrderStatus $orderStatus
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        SubscriptionCollectionFactory $subscriptionCollectionFactory,
        PaymentProfileCollectionFactory $paymentProfileCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        AddressRepositoryInterface $addressRepository,
        Api $api,
        PriceCurrencyInterface $priceHelper,
        Registry $registry,
        SubscriptionOrderCollectionFactory $subscriptionsOrderCollectionFactory,
        OrderStatus $orderStatus,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->paymentProfileCollectionFactory = $paymentProfileCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->addressRepository = $addressRepository;
        $this->api = $api;
        $this->priceHelper = $priceHelper;
        $this->registry = $registry;
        $this->subscriptionsOrderCollectionFactory = $subscriptionsOrderCollectionFactory;
        $this->orderStatus = $orderStatus;
    }

    /**
     * Add 'current' class to 'My Subscriptions' navigation item.
     */
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('customer-account-navigation-vindi-subscriptions')->setClass('current');
        return parent::_prepareLayout();
    }

    /**
     * Get subscription details
     *
     * @return \Vindi\Payment\Model\Subscription|null
     */
    public function getSubscription()
    {
        $subscriptionId = $this->getRequest()->getParam('id');
        if ($subscriptionId) {
            $subscriptionCollection = $this->subscriptionCollectionFactory->create();
            $subscriptionCollection->addFieldToFilter('customer_id', $this->customerSession->getCustomerId());
            $subscriptionCollection->addFieldToFilter('id', $subscriptionId);
            $subscriptionCollection->setPageSize(1);

            return $subscriptionCollection->getFirstItem();
        }
        return null;
    }

    /**
     * Get payment profile details
     *
     * @return \Vindi\Payment\Model\PaymentProfile|null
     */
    public function getPaymentProfile()
    {
        $subscription = $this->getSubscription();
        if ($subscription) {
            $paymentProfileCollection = $this->paymentProfileCollectionFactory->create();
            $paymentProfileCollection->addFieldToFilter('entity_id', $subscription->getPaymentProfileId());
            $paymentProfileCollection->setPageSize(1);

            return $paymentProfileCollection->getFirstItem();
        }
        return null;
    }

    /**
     * Get billing details
     *
     * @return array
     */
    public function getBillingDetails()
    {
        $subscription = $this->getSubscription();
        if ($subscription) {
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addFieldToFilter('vindi_subscription_id', $subscription->getId());

            return $orderCollection->getItems();
        }
        return [];
    }

    /**
     * Get shipping address
     *
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     */
    public function getShippingAddress()
    {
        $customer = $this->customerSession->getCustomer();
        if ($customer) {
            $addressId = $customer->getDefaultShipping();
            if ($addressId) {
                return $this->addressRepository->getById($addressId);
            }
        }
        return null;
    }

    /**
     * Format date
     *
     * @param string $date
     * @param int $format
     * @param bool $showTime
     * @param string|null $timezone
     * @return string
     */
    public function formatDate($date = null, $format = \IntlDateFormatter::SHORT, $showTime = false, $timezone = null)
    {
        try {
            $dateTime = new DateTime($date);
            return $dateTime->format('d/m/Y');
        } catch (Exception $e) {
            return '-';
        }
    }

    /**
     * Format price
     *
     * @param float $amount
     * @return string
     */
    public function formatPrice($amount)
    {
        return $this->priceHelper->format($amount, false);
    }

    /**
     * Get status label
     *
     * @param string $status
     * @return string
     */
    public function getStatusLabel($status)
    {
        switch ($status) {
            case 'active':
                return __('Active');
            case 'canceled':
                return __('Canceled');
            case 'expired':
                return __('Expired');
            default:
                return __('Unknown');
        }
    }

    /**
     * Get customer name
     *
     * @return string
     */
    public function getCustomerName()
    {
        $data = $this->getSubscriptionData();
        return $data['customer']['name'] ?? '';
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        $data = $this->getSubscriptionData();
        return $data['status'] ?? '-';
    }

    /**
     * Get start date
     *
     * @return string
     */
    public function getStartAt()
    {
        $data = $this->getSubscriptionData();
        if (!isset($data['start_at'])) {
            return '-';
        }

        try {
            $startAt = new DateTime($data['start_at']);
            return $startAt->format('d/m/Y');
        } catch (Exception $e) {
            return '-';
        }
    }

    /**
     * Get plan name
     *
     * @return string
     */
    public function getPlanName()
    {
        $data = $this->getSubscriptionData();
        return $data['plan']['name'] ?? '-';
    }

    /**
     * Get plan cycle
     *
     * @return string
     */
    public function getPlanCycle()
    {
        $data = $this->getSubscriptionData();
        return $data['interval'] ?? '-';
    }

    /**
     * Get next billing date
     *
     * @return string
     */
    public function getNextBillingAt()
    {
        $data = $this->getSubscriptionData();
        return isset($data['next_billing_at']) ? $this->dateFormat($data['next_billing_at']) : '-';
    }

    /**
     * Get billing trigger
     *
     * @return string
     */
    public function getBillingTrigger()
    {
        $data = $this->getSubscriptionData();
        if (!isset($data['billing_trigger_type']) || !isset($data['billing_trigger_day'])) {
            return '-';
        }

        $billingTriggerDay = $data['billing_trigger_day'];
        $billingTriggerType = $data['billing_trigger_type'];

        if ($billingTriggerDay == 0) {
            return '1 day after the end';
        }

        switch ($billingTriggerType) {
            case 'beginning_of_period':
                return __('%1 days after the end', $billingTriggerDay);
            case 'end_of_period':
                return __('%1 days before the end', $billingTriggerDay);
            case 'day_of_month':
                return __('Exactly on day %1 of each month', $billingTriggerDay);
            default:
                return '-';
        }
    }

    /**
     * Get products
     *
     * @return array
     */
    public function getProducts()
    {
        $data = $this->getSubscriptionData();
        return $data['product_items'] ?? [];
    }

    /**
     * Get payment method
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        $data = $this->getSubscriptionData();
        return $data['payment_method']['name'] ?? '-';
    }

    /**
     * Get cycle label
     *
     * @param $cycle
     * @return string
     */
    public function getCycleLabel($cycle)
    {
        return is_null($cycle) ? __('Permanent') : $cycle;
    }

    /**
     * Get periods
     *
     * @return array|null
     */
    public function getPeriods()
    {
        if (!$id = $this->getSubscriptionId()) {
            return [];
        }

        if ($this->periods === null) {
            $request = $this->api->request('periods?query=subscription_id%3D' . $id, 'GET');
            $this->periods = $request['periods'] ?? [];
        }

        return $this->periods;
    }

    /**
     * Get discounts
     *
     * @return array
     */
    public function getDiscounts()
    {
        $products = $this->getProducts();
        if (empty($products)) {
            return [];
        }

        $discounts = [];
        foreach ($products as $product) {
            if (empty($product['discounts'])) {
                continue;
            }

            foreach ($product['discounts'] as $discount) {
                $discounts[] = array_merge($discount, ['product' => $product['product']['name']]);
            }
        }

        return $discounts;
    }

    /**
     * Format date
     *
     * @param $date
     * @return string
     */
    public function dateFormat($date)
    {
        try {
            $startAt = new DateTime($date);
            return $startAt->format('d/m/Y');
        } catch (Exception $e) {
            return '-';
        }
    }

    /**
     * Get subscription ID
     *
     * @return int
     */
    public function getSubscriptionId()
    {
        $data = $this->getSubscriptionData();
        return $data['id'] ?? 0;
    }

    /**
     * Get linked orders
     *
     * @return array
     */
    public function getLinkedOrders()
    {
        $subscriptionId = $this->getSubscriptionId();
        if (!$subscriptionId) {
            return [];
        }

        $collection = $this->subscriptionsOrderCollectionFactory->create();
        $collection->addFieldToFilter('subscription_id', $subscriptionId);

        return $collection->getItems();
    }

    /**
     * Get filtered payment profiles
     *
     * @return array
     */
    public function getFilteredPaymentProfiles()
    {
        $subscriptionId = $this->getSubscriptionId();
        if (!$subscriptionId) {
            return [];
        }

        $paymentProfileId = $this->getSubscription()->getPaymentProfileId();
        $paymentProfileCollection = $this->paymentProfileCollectionFactory->create();
        $paymentProfileCollection->addFieldToFilter('entity_id', $paymentProfileId);

        return $paymentProfileCollection->getItems();
    }

    /**
     * Get subscription data
     *
     * @return array|null
     */
    private function getSubscriptionData()
    {
        if ($this->subscriptionData === null) {
            $id = $this->getRequest()->getParam('id');
            try {
                $request = $this->api->request('subscriptions/' . $id, 'GET');
                $this->subscriptionData = $request['subscription'] ?? [];
            } catch (\Exception $e) {
                $this->_redirect('vindi/subscription/index');
            }
        }

        return $this->subscriptionData;
    }

    /**
     * Get order status label
     *
     * @param string $status
     * @return string
     */
    public function getOrderStatusLabel($status)
    {
        return $this->orderStatus->getLabel($status);
    }
}
