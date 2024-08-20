<?php

namespace Vindi\Payment\Model\Vindi;

use Magento\Framework\Exception\LocalizedException;
use Vindi\Payment\Api\PlanInterface;
use Vindi\Payment\Helper\Api;

/**
 * Class Plan
 * @package Vindi\Payment\Model\Vindi
 */
class Plan implements PlanInterface
{
    /**
     * @var Api
     */
    private $api;

    /**
     * Plan constructor.
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
    public function save($data = []): int
    {
        $endpoint = 'plans';
        $method = 'POST';

        if (!empty($data['vindi_id']) && $plan = $this->findOneById($data['vindi_id'])) {
            $endpoint .= '/' . $plan['id'];
            $method = 'PUT';
        } elseif ($plan = $this->findOneByCode($data['code'])) {
            $endpoint .= '/' . $plan['id'];
            $method = 'PUT';
        }

        $response = $this->api->request($endpoint, $method, $data);
        if (!$response) {
            throw new LocalizedException(__('The plan could not be saved'));
        }

        return $response['plan']['id'];
    }

    /**
     * @inheritDoc
     */
    public function findOneByCode($code)
    {
        $response = $this->api->request("plans?query=code%3D{$code}", 'GET');

        if ($response && (1 === count($response['plans'])) && isset($response['plans'][0]['id'])) {
            return reset($response['plans']);
        }

        return false;
    }

    /**
     * Retrieve a plan by its ID
     *
     * @param int $id
     * @return array|bool
     * @throws LocalizedException
     */
    public function findOneById(int $id)
    {
        $response = $this->api->request("plans/{$id}", 'GET');

        if ($response && isset($response['plan']['id'])) {
            return $response['plan'];
        }

        return false;
    }

    /**
     * @param int $page
     * @return bool|mixed
     */
    public function getAllPlans($page = 1)
    {
        return $this->api->request('plans?per_page=100&page=' . $page, 'GET');
    }
}
