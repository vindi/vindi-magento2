<?php
declare(strict_types=1);

namespace Vindi\Payment\Api\Data;

/**
 * Interface VindiSubscriptionItemInterface
 * @package Vindi\Payment\Api\Data
 */
interface VindiSubscriptionItemInterface
{
    const ENTITY_ID             = 'entity_id';
    const SUBSCRIPTION_ID       = 'subscription_id';
    const PRODUCT_ITEM_ID       = 'product_item_id';
    const PRODUCT_NAME          = 'product_name';
    const PRODUCT_CODE          = 'product_code';
    const QUANTITY              = 'quantity';
    const PRICE                 = 'price';
    const PRICING_SCHEMA_ID     = 'pricing_schema_id';
    const PRICING_SCHEMA_TYPE   = 'pricing_schema_type';
    const PRICING_SCHEMA_FORMAT = 'pricing_schema_short_format';
    const STATUS                = 'status';
    const USES                  = 'uses';
    const CYCLES                = 'cycles';
    const DISCOUNT_TYPE         = 'discount_type';
    const DISCOUNT_PERCENTAGE   = 'discount_percentage';
    const CREATED_AT            = 'created_at';
    const UPDATED_AT            = 'updated_at';

    public function getId();

    public function setId($entityId);

    public function getSubscriptionId();

    public function setSubscriptionId($subscriptionId);

    public function getProductItemId();

    public function setProductItemId($productItemId);

    public function getProductName();

    public function setProductName($productName);

    public function getProductCode();

    public function setProductCode($productCode);

    public function getQuantity();

    public function setQuantity($quantity);

    public function getPrice();

    public function setPrice($price);

    public function getPricingSchemaId();

    public function setPricingSchemaId($pricingSchemaId);

    public function getPricingSchemaType();

    public function setPricingSchemaType($pricingSchemaType);

    public function getPricingSchemaFormat();

    public function setPricingSchemaFormat($pricingSchemaFormat);

    public function getStatus();

    public function setStatus($status);

    public function getUses();

    public function setUses($uses);

    public function getCycles();

    public function setCycles($cycles);

    public function getDiscountType();

    public function setDiscountType($discountType);

    public function getDiscountPercentage();

    public function setDiscountPercentage($discountPercentage);

    public function getCreatedAt();

    public function setCreatedAt($createdAt);

    public function getUpdatedAt();

    public function setUpdatedAt($updatedAt);
}
