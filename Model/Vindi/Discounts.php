<?php

namespace Vindi\Payment\Model\Vindi;

use Vindi\Payment\Helper\Api;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Discounts
 *
 * Handles interactions with the discounts via API
 */
class Discounts
{
    /** @var Api */
    protected $api;

    /** @var ManagerInterface */
    protected $messageManager;

    /**
     * @param Api $api
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Api $api,
        ManagerInterface $messageManager
    ) {
        $this->api = $api;
        $this->messageManager = $messageManager;
    }

    /**
     * Create Discount via API
     *
     * @param array $data
     * @return array|bool
     */
    public function createDiscount(array $data)
    {
        $response = $this->api->request('discounts', 'POST', $data);
        if (isset($response["discount"]["id"])) {
            return $response;
        }

        $this->messageManager->addErrorMessage(__('Failed to create discount. Check the data and try again.'));
        return false;
    }

    /**
     * Retrieve Discount by ID via API
     *
     * @param string $discountId
     * @return array|bool
     */
    public function getDiscountById($discountId)
    {
        $response = $this->api->request("discounts/{$discountId}", 'GET');
        if (isset($response['id'])) {
            return $response;
        }

        $this->messageManager->addErrorMessage(__('Discount not found.'));
        return false;
    }

    /**
     * Update Discount via API
     *
     * @param string $discountId
     * @param array $data
     * @return array|bool
     */
    public function updateDiscount($discountId, array $data)
    {
        $response = $this->api->request("discounts/{$discountId}", 'PUT', $data);
        if (isset($response['id'])) {
            return $response;
        }

        $this->messageManager->addErrorMessage(__('Failed to update discount. Check the data and try again.'));
        return false;
    }

    /**
     * Delete Discount by ID via API
     *
     * @param string $discountId
     * @return bool
     */
    public function deleteDiscount($discountId)
    {
        $response = $this->api->request("discounts/{$discountId}", 'DELETE');
        if (!isset($response['error'])) {
            return true;
        }

        $this->messageManager->addErrorMessage(__('Failed to delete discount.'));
        return false;
    }
}
