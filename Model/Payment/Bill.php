<?php

namespace Vindi\Payment\Model\Payment;

use Vindi\Payment\Helper\Api;

/**
 * Class Bill
 * @package Vindi\Payment\Model\Payment
 */
class Bill
{

    const PAID_STATUS = 'paid';
    const REVIEW_STATUS = 'review';
    const FRAUD_REVIEW_STATUS = 'fraud_review';
    const WAITING_STATUS = 'waiting';

    /**
     * @var Api
     */
    private $api;

    /**
     * @param Api $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    /**
     * @param array $body
     *
     * @return int|bool
     */
    public function create($body)
    {
        if ($response = $this->api->request('bills', 'POST', $body)) {
            return $response['bill'];
        }

        return false;
    }

    /**
     * @param $billId
     */
    public function delete($billId)
    {
        $this->api->request("bills/{$billId}", 'DELETE');
    }

    /**
     * @param $billId
     *
     * @return array|bool
     */
    public function getBill($billId)
    {
        $response = $this->api->request("bills/{$billId}", 'GET');

        if (! $response || ! isset($response['bill'])) {
            return false;
        }

        return $response['bill'];
    }
}
