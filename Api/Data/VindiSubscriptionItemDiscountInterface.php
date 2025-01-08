<?php
declare(strict_types=1);

namespace Vindi\Payment\Api\Data;

/**
 * Interface VindiSubscriptionItemDiscountInterface
 * @package Vindi\Payment\Api\Data
 */
interface VindiSubscriptionItemDiscountInterface
{
    const ENTITY_ID           = 'entity_id';
    const SUBSCRIPTION_ID     = 'subscription_id';
    const PRODUCT_ITEM_ID     = 'product_item_id';
    const MAGENTO_PRODUCT_ID  = 'magento_product_id';
    const MAGENTO_PRODUCT_SKU = 'magento_product_sku';
    const DISCOUNT_TYPE       = 'discount_type';
    const PERCENTAGE          = 'percentage';
    const AMOUNT              = 'amount';
    const QUANTITY            = 'quantity';
    const CYCLES              = 'cycles';
    const CREATED_AT          = 'created_at';
    const UPDATED_AT          = 'updated_at';

    public function getId();

    public function setId($entityId);

    public function getSubscriptionId();

    public function setSubscriptionId($subscriptionId);

    public function getProductItemId();

    public function setProductItemId($productItemId);

    public function getMagentoProductId();

    public function setMagentoProductId($magentoProductId);

    public function getMagentoProductSku();

    public function setMagentoProductSku($magentoProductSku);

    public function getDiscountType();

    public function setDiscountType($discountType);

    public function getPercentage();

    public function setPercentage($percentage);

    public function getAmount();

    public function setAmount($amount);

    public function getQuantity();

    public function setQuantity($quantity);

    public function getCycles();

    public function setCycles($cycles);

    public function getCreatedAt();

    public function setCreatedAt($createdAt);

    public function getUpdatedAt();

    public function setUpdatedAt($updatedAt);
}
