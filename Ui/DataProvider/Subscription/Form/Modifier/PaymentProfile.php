<?php

namespace Vindi\Payment\Ui\DataProvider\Subscription\Form\Modifier;

use Magento\Framework\Registry;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Vindi\Payment\Helper\Api;

/**
 * Class PaymentProfile
 * @package Vindi\Payment\Ui\DataProvider\Subscription\Form\Modifier
 */
class PaymentProfile implements ModifierInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Api
     */
    private $api;

    /**
     * @var array|null
     */
    private $subscriptions;

    /**
     * PaymentProfile constructor.
     * @param Registry $registry
     * @param Api $api
     */
    public function __construct(
        Registry $registry,
        Api $api
    ) {
        $this->registry = $registry;
        $this->api = $api;
    }

    /**
     * @inheritDoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get the customer ID associated with the subscription data
     *
     * @return int
     */
    public function getCustomerId()
    {
        $data = $this->getSubscriptionData();
        if (is_array($data) && array_key_exists('customer', $data)) {
            return $data['customer']['id'];
        }

        return 0;
    }

    /**
     * Retrieve subscription data from the API
     *
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
