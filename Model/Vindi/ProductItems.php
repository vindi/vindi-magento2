<?php

namespace Vindi\Payment\Model\Vindi;

use Vindi\Payment\Helper\Api;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class ProductItems
 *
 * Handles interactions with the product items via API
 */
class ProductItems
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
     * Create Product Item via API
     *
     * @param array $data
     * @return array|bool
     */
    public function createProductItem(array $data)
    {
        $response = $this->api->request('product_items', 'POST', $data);
        if (isset($response['product_item']['id'])) {
            return $response['product_item'];
        }

        $this->messageManager->addErrorMessage(__('Failed to create product item. Check the data and try again.'));
        return false;
    }

    /**
     * Retrieve Product Item by ID via API
     *
     * @param string $productItemId
     * @return array|bool
     */
    public function getProductItemById($productItemId)
    {
        $response = $this->api->request("product_items/{$productItemId}", 'GET');
        if (isset($response['product_item']['id'])) {
            return $response['product_item'];
        }

        $this->messageManager->addErrorMessage(__('Product item not found.'));
        return false;
    }

    /**
     * Update Product Item via API
     *
     * @param string $productItemId
     * @param array $data
     * @return array|bool
     */
    public function updateProductItem($productItemId, array $data)
    {
        $response = $this->api->request("product_items/{$productItemId}", 'PUT', $data);
        if (isset($response['product_item']['id'])) {
            return $response['product_item'];
        }

        $this->messageManager->addErrorMessage(__('Failed to update product item. Check the data and try again.'));
        return false;
    }

    /**
     * Delete Product Item by ID via API
     *
     * @param string $productItemId
     * @return bool
     */
    public function deleteProductItem($productItemId)
    {
        $response = $this->api->request("product_items/{$productItemId}", 'DELETE');
        if ($response === true) {
            return true;
        }

        $this->messageManager->addErrorMessage(__('Failed to delete product item.'));
        return false;
    }
}
