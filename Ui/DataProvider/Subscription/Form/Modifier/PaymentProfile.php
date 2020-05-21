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
    private $subscriptions;
    /**
     * @var Api
     */
    private $api;

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
        $meta['general'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('General'),
                        'sortOrder' => 50,
                        'collapsible' => false,
                        'componentType' => Fieldset::NAME
                    ]
                ]
            ],
            'children' => [
                'payment_profile' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'radioset',
                                'componentType' => Field::NAME,
                                'options' => $this->getPaymentProfiles(),
                                'visible' => 1,
                                'required' => 1,
                                'label' => __('payment profile')
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $meta;
    }

    /**
     * @return array
     */
    public function getPaymentProfiles()
    {
        $options = [];
        $customer = $this->getCustomerId();
        $request = $this->api->request('payment_profiles?query=customer_id%3D' . $customer . '&status=active', 'GET');
        if (!is_array($request) && !array_key_exists('payment_profiles', $request)) {
            return $options;
        }
        foreach ($request['payment_profiles'] as $profile) {
            $options[] = [
                'value' => $profile['id'],
                'label' => $profile['payment_method']['public_name'] . ' - ' . $profile['payment_company']['name'] . ' ('. $profile['card_number_last_four'] .  ')'
            ];
        }

        return $options;
    }

    /**
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
