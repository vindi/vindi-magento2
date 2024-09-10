<?php

namespace Vindi\Payment\Block\Adminhtml\Subscription;

use DateTime;
use Exception;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Vindi\Payment\Helper\Api;
use Vindi\Payment\Model\ResourceModel\SubscriptionOrder\CollectionFactory as SubscriptionOrderCollectionFactory;
use Vindi\Payment\Model\SubscriptionFactory;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\CollectionFactory as VindiSubscriptionItemCollectionFactory;
use Vindi\Payment\Model\VindiSubscriptionItemFactory;

/**
 * Class View
 *
 * @package Vindi\Payment\Block\Adminhtml\Subscription
 */
class View extends Container
{
    /**
     * @var array|null
     */
    private $subscriptions = null;

    /**
     * @var array|null
     */
    private $periods = null;

    /**
     * @var Api
     */
    private $api;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var SubscriptionOrderCollectionFactory
     */
    private $subscriptionsOrderCollectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceHelper;

    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;

    /**
     * @var VindiSubscriptionItemCollectionFactory
     */
    private $vindiSubscriptionItemCollectionFactory;

    /**
     * @var VindiSubscriptionItemFactory
     */
    private $vindiSubscriptionItemFactory;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param SubscriptionOrderCollectionFactory $subscriptionsOrderCollectionFactory
     * @param Api $api
     * @param PriceCurrencyInterface $priceHelper
     * @param SubscriptionFactory $subscriptionFactory
     * @param VindiSubscriptionItemCollectionFactory $vindiSubscriptionItemCollectionFactory
     * @param VindiSubscriptionItemFactory $vindiSubscriptionItemFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SubscriptionOrderCollectionFactory $subscriptionsOrderCollectionFactory,
        Api $api,
        PriceCurrencyInterface $priceHelper,
        SubscriptionFactory $subscriptionFactory,
        VindiSubscriptionItemCollectionFactory $vindiSubscriptionItemCollectionFactory,
        VindiSubscriptionItemFactory $vindiSubscriptionItemFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->api = $api;
        $this->registry = $registry;
        $this->subscriptionsOrderCollectionFactory = $subscriptionsOrderCollectionFactory;
        $this->priceHelper = $priceHelper;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->vindiSubscriptionItemCollectionFactory = $vindiSubscriptionItemCollectionFactory;
        $this->vindiSubscriptionItemFactory = $vindiSubscriptionItemFactory;
    }

    /**
     * @return Container
     */
    protected function _prepareLayout()
    {
        $this->buttonList->add(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getBackUrl() . '\')',
                'class' => 'back'
            ]
        );

        $this->buttonList->add('vindi_payment_subscription_edit', [
            'id' => 'vindi_payment_subscription_edit',
            'label' => __('Edit Subscription'),
            'class' => 'primary',
            'button_class' => 'add',
            'onclick' => 'setLocation(\'' . $this->getEditUrl() . '\')'
        ]);

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }

    /**
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getUrl('vindi_payment/subscription/edit', [
            'id' => $this->getSubscriptionId()
        ]);
    }

    /**
     * @return string
     */
    public function getCustomerName()
    {
        $data = $this->getSubscriptionData();
        return $data['customer']['name'] ?? '';
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        $data = $this->getSubscriptionData();
        return $data['status'] ?? '-';
    }

    /**
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
     * @return string
     */
    public function getPlanName()
    {
        $data = $this->getSubscriptionData();
        return $data['plan']['name'] ?? '-';
    }

    /**
     * @return string
     */
    public function getPlanCycle()
    {
        $data = $this->getSubscriptionData();
        if (isset($data['interval'], $data['interval_count'])) {
            $intervalLabels = [
                'days' => __('day(s)'),
                'weeks' => __('week(s)'),
                'months' => __('month(s)'),
                'years' => __('year(s)')
            ];

            $interval = $data['interval'];
            $intervalCount = $data['interval_count'];

            return isset($intervalLabels[$interval])
                ? __('Every %1 %2', $intervalCount, $intervalLabels[$interval])
                : '-';
        }

        return '-';
    }

    /**
     * @return string
     */
    public function getPlanDuration()
    {
        $data = $this->getSubscriptionData();
        if (isset($data['billing_cycles'])) {
            $billingCycle = $data['billing_cycles'];
            return ($billingCycle === null || $billingCycle < 0)
                ? __('Permanent')
                : __('%1 cycles', $billingCycle);
        }

        return __('Permanent');
    }

    /**
     * @return string
     */
    public function getNextBillingAt()
    {
        $data = $this->getSubscriptionData();
        return isset($data['next_billing_at']) ? $this->dateFormat($data['next_billing_at']) : '-';
    }

    /**
     * @return string
     */
    public function getBillingTrigger()
    {
        $data = $this->getSubscriptionData();
        if (!isset($data['billing_trigger_type'], $data['billing_trigger_day'])) {
            return '-';
        }

        $billingTriggerDay  = $data['billing_trigger_day'];
        $billingTriggerType = $data['billing_trigger_type'];

        if ($billingTriggerType === 'day_of_month') {
            return __('Day %1 of the month', $billingTriggerDay);
        }

        if ($billingTriggerDay == 0) {
            return $billingTriggerType === 'beginning_of_period'
                ? __('Exactly on the day of the start of the period')
                : __('Exactly on the day of the end of the period');
        }

        $billingTriggerDayLabel = ($billingTriggerDay > 0) ? __('after') : __('before');
        $billingTriggerTypeLabel = $billingTriggerType === 'beginning_of_period'
            ? __('start of the period')
            : __('end of the period');

        return __('%1 days', abs($billingTriggerDay)) . ' ' . $billingTriggerDayLabel . ' ' . $billingTriggerTypeLabel;
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        $data = $this->getSubscriptionData();
        return $data['product_items'] ?? [];
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        $data = $this->getSubscriptionData();
        return $data['payment_method']['name'] ?? '-';
    }

    /**
     * @param $cycle
     * @return string
     */
    public function getCycleLabel($cycle)
    {
        return is_null($cycle) ? __('Permanent') : $cycle;
    }

    /**
     * @return array|null
     */
    public function getPeriods()
    {
        if (!$id = $this->getSubscriptionId()) {
            return [];
        }

        if ($this->periods === null) {
            $request = $this->api->request('periods?query=subscription_id%3D' . $id, 'GET');
            if (is_array($request) && isset($request['periods'])) {
                $this->periods = $request['periods'];
            }
        }

        return $this->periods;
    }

    /**
     * @return array|mixed
     */
    public function getDiscounts()
    {
        $products = $this->getProducts();
        if (empty($products)) {
            return [];
        }

        $discounts = [];
        foreach ($products as $key => $product) {
            if (empty($product['discounts'])) {
                continue;
            }

            foreach ($product['discounts'] as $discount) {
                $discounts[$key] = array_merge($discount, [
                    'product' => $product['product']['name']
                ]);
            }
        }

        return $discounts;
    }

    /**
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
     * @return int
     */
    public function getSubscriptionId()
    {
        $data = $this->getSubscriptionData();
        return $data['id'] ?? 0;
    }

    /**
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
     * @param $amount
     * @return string
     */
    public function formatPrice($amount)
    {
        return $this->priceHelper->format($amount, false);
    }

    /**
     * Fetch subscription data directly from the API.
     *
     * @param int $subscriptionId
     * @return array|null
     */
    public function fetchSubscriptionDataFromApi($subscriptionId)
    {
        $request = $this->api->request('subscriptions/' . $subscriptionId, 'GET');
        if (is_array($request) && isset($request['subscription'])) {
            return $request['subscription'];
        }

        return null;
    }

    /**
     * Check if subscription items are saved in the database and save them if not.
     */
    public function checkAndSaveSubscriptionItems()
    {
        $subscriptionId = $this->getSubscriptionId();
        if (!$subscriptionId) {
            return;
        }

        $itemsCollection = $this->vindiSubscriptionItemCollectionFactory->create();
        $itemsCollection->addFieldToFilter('subscription_id', $subscriptionId);

        if ($itemsCollection->getSize() == 0) {
            $subscriptionData = $this->fetchSubscriptionDataFromApi($subscriptionId);
            if (isset($subscriptionData['product_items'])) {
                foreach ($subscriptionData['product_items'] as $item) {
                    $subscriptionItem = $this->vindiSubscriptionItemFactory->create();
                    $subscriptionItem->setSubscriptionId($subscriptionId);
                    $subscriptionItem->setProductItemId($item['id']);
                    $subscriptionItem->setProductName($item['product']['name']);
                    $subscriptionItem->setProductCode($item['product']['code']);
                    $subscriptionItem->setQuantity($item['quantity']);
                    $subscriptionItem->setPrice($item['pricing_schema']['price']);
                    $subscriptionItem->setPricingSchemaId($item['pricing_schema']['id']);
                    $subscriptionItem->setPricingSchemaType($item['pricing_schema']['schema_type']);
                    $subscriptionItem->setPricingSchemaFormat($item['pricing_schema']['schema_format'] ?? 'N/A');
                    $subscriptionItem->save();
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getSubscriptionData()
    {
        if ($this->subscriptions === null) {
            $id = $this->registry->registry('vindi_payment_subscription_id');
            $subscriptionModel = $this->subscriptionFactory->create()->load($id);
            $responseData = $subscriptionModel->getData('response_data');

            if ($responseData) {
                $this->subscriptions = json_decode($responseData, true);
            } else {
                $this->subscriptions = $this->fetchSubscriptionDataFromApi($id);

                if ($this->subscriptions) {
                    $subscriptionModel->setData('response_data', json_encode($this->subscriptions));
                    $subscriptionModel->save();
                }
            }
        }

        return $this->subscriptions;
    }
}
