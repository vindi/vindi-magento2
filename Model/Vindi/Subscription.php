<?php

namespace Vindi\Payment\Model\Vindi;

use Vindi\Payment\Api\SubscriptionInterface;
use Vindi\Payment\Helper\Api;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Subscription constructor.
     * @param Api $api
     * @param LoggerInterface $logger
     */
    public function __construct(
        Api $api,
        LoggerInterface $logger
    ) {
        $this->api = $api;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function create($data = [])
    {
        try {
            if ($response = $this->api->request('subscriptions', 'POST', $data)) {
                return $response;
            }
        } catch (\Exception $e) {
            $this->logger->error(__('Error while creating subscription: ' . $e->getMessage()));
        }

        return false;
    }

    /**
     * @param $id
     */
    public function deleteAndCancelBills($id)
    {
        try {
            $this->api->request("subscriptions/{$id}?cancel_bills=true", 'DELETE');
        } catch (\Exception $e) {
            $this->logger->error(__('Error while deleting subscription and canceling bills: ' . $e->getMessage()));
        }
    }

    /**
     * Retrieve subscription data by ID from the API
     *
     * @param int $subscriptionId
     * @return array|null
     */
    public function getSubscriptionById($subscriptionId)
    {
        try {
            $response = $this->api->request("subscriptions/{$subscriptionId}", 'GET');
            if (is_array($response) && isset($response['subscription'])) {
                return $response['subscription'];
            }
        } catch (\Exception $e) {
            $this->logger->error(__('Error while retrieving subscription by ID: ' . $e->getMessage()));
        }

        return null;
    }
}

