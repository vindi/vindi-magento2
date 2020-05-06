<?php

namespace Vindi\Payment\Block\Adminhtml\Subscription;

use DateTime;
use Exception;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Vindi\Payment\Helper\Api;

/**
 * Class View
 * @package Vindi\Payment\Block\Adminhtml\Subscription
 */
class View extends Container
{
    /**
     * @var array
     */
    private $subscriptions = null;
    /**
     * @var array
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
     * View constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Api $api
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Api $api,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->api = $api;
        $this->registry = $registry;
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
        if (array_key_exists('customer', $data)) {
            return $data['customer']['name'];
        }

        return '';
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        $data = $this->getSubscriptionData();
        if (array_key_exists('status', $data)) {
            return $data['status'];
        }

        return '-';
    }

    /**
     * @return string
     */
    public function getStartAt()
    {
        $data = $this->getSubscriptionData();
        if (!array_key_exists('start_at', $data)) {
            return '-';
        }

        try {
            $startAt = new DateTime($data['start_at']);
            return $startAt->format('d/m/Y');
        } catch (Exception $e) {
        }

        return '-';
    }

    /**
     * @return string
     */
    public function getPlanName()
    {
        $data = $this->getSubscriptionData();
        if (array_key_exists('plan', $data)) {
            return $data['plan']['name'];
        }

        return '-';
    }

    /**
     * @return string
     */
    public function getPlanCycle()
    {
        $data = $this->getSubscriptionData();
        if (array_key_exists('interval', $data)) {
            return $data['interval'];
        }

        return '-';
    }

    /**
     * @return string
     */
    public function getNextBillingAt()
    {
        $data = $this->getSubscriptionData();
        if (!array_key_exists('next_billing_at', $data)) {
            return '-';
        }

        return $this->dateFormat($data['next_billing_at']);
    }

    /**
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

        $billingTriggerDay = $data['billing_trigger_day'];
        $billingTriggerType = $data['billing_trigger_type'];

        if ($billingTriggerDay == 0) {
            return '1 dia após o término';
        }

        if ($billingTriggerType == 'beginning_of_period') {
            return __('%1 dias após o término', $billingTriggerDay);
        }

        if ($billingTriggerType == 'end_of_period') {
            return __('%1 dias antes do término', $billingTriggerDay);
        }

        if ($billingTriggerType == 'day_of_month') {
            return __('Exatamente no dia %1 de cada mês', $billingTriggerDay);
        }

        return '-';
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        $data = $this->getSubscriptionData();
        if (!array_key_exists('product_items', $data)) {
            return [];
        }

        return $data['product_items'];
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        $data = $this->getSubscriptionData();
        if (array_key_exists('payment_method', $data)) {
            return $data['payment_method']['name'];
        }

        return '-';
    }

    /**
     * @param $cycle
     * @return string
     */
    public function getCycleLabel($cycle)
    {
        if (is_null($cycle)) {
            return 'Permanente';
        }

        return $cycle;
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
            if (is_array($request) && array_key_exists('periods', $request)) {
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
        }

        return '-';
    }

    /**
     * @return int
     */
    public function getSubscriptionId()
    {
        $data = $this->getSubscriptionData();
        if (array_key_exists('id', $data)) {
            return $data['id'];
        }

        return 0;
    }

    /**
     * @return array|null
     */
    private function getSubscriptionData()
    {
        if ($this->subscriptions === null) {
            $id = $this->registry->registry('vindi_payment_subscription_id');
            $request = $this->api->request('subscriptions/'.$id, 'GET');
            if (is_array($request) && array_key_exists('subscription', $request)) {
                $this->subscriptions = $request['subscription'];
            }
        }

        return $this->subscriptions;
    }
}
