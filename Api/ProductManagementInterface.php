<?php

namespace Vindi\Payment\Api;

use Magento\Catalog\Api\Data\ProductInterface as MagentoProductInterface;

/**
 * Interface ProductManagementInterface
 * @package Vindi\Payment\Api
 */
interface ProductManagementInterface
{
    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function findOrCreateProductsToSubscription(\Magento\Sales\Model\Order $order);

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function findOrCreateProductsFromOrder(\Magento\Sales\Model\Order $order);

    /**
     * Find or create a product directly in Vindi Payments by providing the Magento product
     *
     * @param MagentoProductInterface $product
     * @return int Vindi Product ID
     */
    public function findOrCreate(MagentoProductInterface $product);
}
