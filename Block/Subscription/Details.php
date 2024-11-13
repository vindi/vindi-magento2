<?php
namespace Vindi\Payment\Block\Subscription;

use DateTime;
use Exception;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Vindi\Payment\Model\Config\Source\Subscription\PaymentMethod;
use Vindi\Payment\Model\ResourceModel\Subscription\CollectionFactory as SubscriptionCollectionFactory;
use Vindi\Payment\Model\ResourceModel\PaymentProfile\CollectionFactory as PaymentProfileCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Vindi\Payment\Model\Vindi\Subscription as VindiSubscription;
use Vindi\Payment\Helper\Api;
use Vindi\Payment\Model\ResourceModel\SubscriptionOrder\CollectionFactory as SubscriptionOrderCollectionFactory;
use Magento\Framework\Registry;
use Vindi\Payment\Model\Config\Source\OrderStatus;
use Magento\Payment\Helper\Data as PaymentHelper;
use Vindi\Payment\Model\Config\Source\CardImages as CardImagesSource;
use Vindi\Payment\Model\Config\Source\Subscription\PaymentMethod as SourcePaymentMethod;
use Vindi\Payment\Model\SubscriptionFactory;

/**
 * Class Details
 * @package Vindi\Payment\Block\Subscription
 */
class Details extends Template
{
    protected $customerSession;
    protected $subscriptionCollectionFactory;
    protected $paymentProfileCollectionFactory;
    protected $orderCollectionFactory;
    protected $addressRepository;
    private $vindiSubscription;
    private $api;
    protected $priceHelper;
    private $registry;
    private $subscriptionsOrderCollectionFactory;
    private $subscriptionData = null;
    private $periods = null;
    private $orderStatus;
    private $paymentHelper;
    private $paymentMethod;
    private $creditCardTypeSource;
    private $subscriptionFactory;

    /**
     * Details constructor.
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param SubscriptionCollectionFactory $subscriptionCollectionFactory
     * @param PaymentProfileCollectionFactory $paymentProfileCollectionFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param VindiSubscription $vindiSubscription
     * @param Api $api
     * @param PriceCurrencyInterface $priceHelper
     * @param Registry $registry
     * @param SubscriptionOrderCollectionFactory $subscriptionsOrderCollectionFactory
     * @param OrderStatus $orderStatus
     * @param PaymentHelper $paymentHelper
     * @param SourcePaymentMethod $paymentMethod
     * @param CardImagesSource $creditCardTypeSource
     * @param SubscriptionFactory $subscriptionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        SubscriptionCollectionFactory $subscriptionCollectionFactory,
        PaymentProfileCollectionFactory $paymentProfileCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        AddressRepositoryInterface $addressRepository,
        VindiSubscription $vindiSubscription,
        Api $api,
        PriceCurrencyInterface $priceHelper,
        Registry $registry,
        SubscriptionOrderCollectionFactory $subscriptionsOrderCollectionFactory,
        OrderStatus $orderStatus,
        PaymentHelper $paymentHelper,
        SourcePaymentMethod $paymentMethod,
        CardImagesSource $creditCardTypeSource,
        SubscriptionFactory $subscriptionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->paymentProfileCollectionFactory = $paymentProfileCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->addressRepository = $addressRepository;
        $this->vindiSubscription = $vindiSubscription;
        $this->api = $api;
        $this->priceHelper = $priceHelper;
        $this->registry = $registry;
        $this->subscriptionsOrderCollectionFactory = $subscriptionsOrderCollectionFactory;
        $this->orderStatus = $orderStatus;
        $this->paymentHelper = $paymentHelper;
        $this->paymentMethod = $paymentMethod;
        $this->creditCardTypeSource = $creditCardTypeSource;
        $this->subscriptionFactory = $subscriptionFactory;
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
        if (array_key_exists('interval', $data) && array_key_exists('interval_count', $data)) {
            $interval = $data['interval'];
            $intervalCount = $data['interval_count'];
            $intervalLabels = [
                'days'   => __('day(s)'),
                'weeks'  => __('week(s)'),
                'months' => __('month(s)'),
                'years'  => __('year(s)')
            ];

            if (array_key_exists($interval, $intervalLabels)) {
                return __('Every %1 %2', $intervalCount, $intervalLabels[$interval]);
            }
        }

        return '-';
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
        if (!array_key_exists('billing_trigger_type', $data)
            || !array_key_exists('billing_trigger_day', $data)
        ) {
            return '-';
        }

        $billingTriggerDay  = $data['billing_trigger_day'];
        $billingTriggerType = $data['billing_trigger_type'];

        if ($billingTriggerType == 'day_of_month') {
            return __('Day %1 of the month', $billingTriggerDay);
        }

        if ($billingTriggerDay == 0) {
            if ($billingTriggerType == 'beginning_of_period') {
                return __('Exactly on the day of the start of the period');
            }

            return __('Exactly on the day of the end of the period');
        }

        $billingTriggerDayLabel = __('before');

        if ($billingTriggerDay > 0) {
            $billingTriggerDayLabel = __('after');
        }

        $billingTriggerTypeLabel = __('end of the period');

        if ($billingTriggerType == 'beginning_of_period') {
            $billingTriggerTypeLabel = __('start of the period');
        }

        return __('%1 days', $billingTriggerDay) . ' ' . $billingTriggerDayLabel . ' ' . $billingTriggerTypeLabel;
    }

    /**
     * @return string
     */
    public function getPlanDuration()
    {
        $data = $this->getSubscriptionData();
        if (array_key_exists('billing_cycles', $data)) {
            $billingCycle = $data["billing_cycles"];

            if ($billingCycle == null || empty($billingCycle) || $billingCycle < 0) {
                return __('Permanent');
            }

            return __('%1 cycles', $billingCycle);
        }

        return __('Permanent');
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
     * Get payment method label
     *
     * @param $paymentMethodValue
     * @return mixed
     */
    public function getPaymentMethodLabel($paymentMethodValue)
    {
        $options = $this->paymentMethod->toOptionArray();
        foreach ($options as $option) {
            if ($option['value'] == $paymentMethodValue) {
                return $option['label'];
            }
        }
        return $paymentMethodValue;
    }

    /**
     * Get payment method image
     *
     * @return string
     */
    public function getPaymentMethodImage()
    {
        $data = $this->getSubscriptionData();

        $paymentMethodCode = '';

        if (isset($data['payment_method']['code'])) {
            $paymentMethodCode = $data['payment_method']['code'];
            $imageUrl = '';

            switch ($paymentMethodCode) {
                case 'pix':
                case 'pix_bank_slip':
                    $imageUrl = $this->getViewFileUrl('Vindi_Payment::images/payment_methods/pix.png');
                    break;
                case 'bank_slip':
                    $imageUrl = $this->getViewFileUrl('Vindi_Payment::images/payment_methods/bankslip.png');
                    break;
                case 'credit_card':
                case 'debit_card':
                    return '';
                default:
                    return '';
            }

            return '<img src="' . $imageUrl . '" alt="' . $paymentMethodCode . '" style="width: 200px; height: auto;" />';
        }

        return '';
    }

    /**
     * Get cycle label
     *
     * @param $cycle
     * @param $uses
     * @return string
     */
    public function getCycleLabel($cycle, $uses = null)
    {
        if (is_null($cycle)) {
            return __('Permanent');
        }

        if (is_null($uses)) {
            return $cycle;
        }

        return __('Temporary (%1/%2)', $uses, $cycle);
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

        $paymentProfileId = $this->getSubscription()->getPaymentProfile();
        $paymentProfileCollection = $this->paymentProfileCollectionFactory->create();
        $paymentProfileCollection->addFieldToFilter('payment_profile_id', $paymentProfileId);

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

            $subscriptionModel = $this->subscriptionFactory->create()->load($id);

            $responseData = $subscriptionModel->getData('response_data');

            if ($responseData) {
                $this->subscriptionData = json_decode($responseData, true);
            } else {
                $this->subscriptionData = $this->vindiSubscription->getSubscriptionById($id);

                if ($this->subscriptionData) {
                    $subscriptionModel->setData('response_data', json_encode($this->subscriptionData));
                    $subscriptionModel->save();
                }
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

    /**
     * Get credit card image
     *
     * @param string $ccType
     * @return string|null
     */
    public function getCreditCardImage($ccType)
    {
        $creditCardOptionArray = $this->creditCardTypeSource->toOptionArray();

        foreach ($creditCardOptionArray as $creditCardOption) {
            if ($creditCardOption['label']->getText() == $ccType) {
                return $creditCardOption['value'];
            }
        }
        return null;
    }
}

