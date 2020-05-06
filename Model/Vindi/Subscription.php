<?php

namespace Vindi\Payment\Model\Vindi;

use Vindi\Payment\Api\SubscriptionInterface;
use Vindi\Payment\Helper\Api;

/**
 * Class Subscription
 * @package Vindi\Payment\Model\Vindi
 */
class Subscription implements SubscriptionInterface
{
    /**
     * @var Api
     */
    private $api;

    /**
     * Subscription constructor.
     * @param Api $api
     */
    public function __construct(
        Api $api
    ) {
        $this->api = $api;
    }

    /**
     * @inheritDoc
     */
    public function create($data = [])
    {
        if ($response = $this->api->request('subscriptions', 'POST', $data)) {
            return $response;
        }

        return false;
    }
}
