<?php

namespace Vindi\Payment\Ui\DataProvider\Subscription\Form\Modifier;

use Magento\Framework\Registry;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Vindi\Payment\Helper\Api;
use Vindi\Payment\Model\ResourceModel\PaymentProfile\Collection as PaymentProfileCollection;

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
     * @var PaymentProfileCollection
     */
    private $paymentProfileCollection;

    /**
     * @var array|null
     */
    private $subscriptions;
    /**
     * @var Api
     */
    private $api;

    /**
     * PaymentProfile constructor.
     * @param Registry $registry
     * @param Api $api
     * @param PaymentProfileCollection $paymentProfileCollection
     */
    public function __construct(
        Registry $registry,
        Api $api,
        PaymentProfileCollection $paymentProfileCollection
    ) {
        $this->registry = $registry;
        $this->api = $api;
        $this->paymentProfileCollection = $paymentProfileCollection;
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
     * Get payment profiles from the collection
     *
     * @return array
     */
    public function getPaymentProfiles()
    {
        $data = $this->getSubscriptionData();

        if (isset($data['payment_method']['code']) && $data['payment_method']['code'] !== 'credit_card') {
            return [];
        }

        $options = [];
        $customerId = $this->getCustomerId();

        if ($customerId) {
            $paymentProfileCollection = $this->paymentProfileCollection->addFieldToFilter('vindi_customer_id', $customerId)
                ->setOrder('created_at', 'DESC');

            foreach ($paymentProfileCollection as $profile) {
                $ccName = $profile->getCcName() ? ' (' . $profile->getCcName() . ')' : '';
                $options[] = [
                    'value' => $profile->getPaymentProfileId(),
                    'label' => $profile->getCcType() . '****' . $profile->getCcLast4() . $ccName
                ];
            }
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
