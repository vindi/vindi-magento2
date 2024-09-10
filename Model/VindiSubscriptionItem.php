<?php
namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\VindiSubscriptionItemInterface;

/**
 * Class VindiSubscriptionItem
 *
 * @package Vindi\Payment\Model
 */
class VindiSubscriptionItem extends \Magento\Framework\Model\AbstractModel implements VindiSubscriptionItemInterface
{
    /**
     * @var string
     */
    protected function _construct()
    {
        $this->_init('Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @param int $entityId
     * @return $this
     */
    public function setId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @return int
     */
    public function getSubscriptionId()
    {
        return $this->getData(self::SUBSCRIPTION_ID);
    }

    /**
     * @param int $subscriptionId
     * @return $this
     */
    public function setSubscriptionId($subscriptionId)
    {
        return $this->setData(self::SUBSCRIPTION_ID, $subscriptionId);
    }

    /**
     * @return int
     */
    public function getProductItemId()
    {
        return $this->getData(self::PRODUCT_ITEM_ID);
    }

    /**
     * @param int $productItemId
     * @return $this
     */
    public function setProductItemId($productItemId)
    {
        return $this->setData(self::PRODUCT_ITEM_ID, $productItemId);
    }

    /**
     * @return string
     */
    public function getProductName()
    {
        return $this->getData(self::PRODUCT_NAME);
    }

    /**
     * @param string $productName
     * @return $this
     */
    public function setProductName($productName)
    {
        return $this->setData(self::PRODUCT_NAME, $productName);
    }

    /**
     * @return string
     */
    public function getProductCode()
    {
        return $this->getData(self::PRODUCT_CODE);
    }

    /**
     * @param string $productCode
     * @return $this
     */
    public function setProductCode($productCode)
    {
        return $this->setData(self::PRODUCT_CODE, $productCode);
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->getData(self::QUANTITY);
    }

    /**
     * @param int $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        return $this->setData(self::QUANTITY, $quantity);
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * @param float $price
     * @return $this
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * @return int
     */
    public function getPricingSchemaId()
    {
        return $this->getData(self::PRICING_SCHEMA_ID);
    }

    /**
     * @param int $pricingSchemaId
     * @return $this
     */
    public function setPricingSchemaId($pricingSchemaId)
    {
        return $this->setData(self::PRICING_SCHEMA_ID, $pricingSchemaId);
    }

    /**
     * @return string
     */
    public function getPricingSchemaType()
    {
        return $this->getData(self::PRICING_SCHEMA_TYPE);
    }

    /**
     * @param $pricingSchemaType
     * @return $this
     */
    public function setPricingSchemaType($pricingSchemaType)
    {
        return $this->setData(self::PRICING_SCHEMA_TYPE, $pricingSchemaType);
    }

    /**
     * @return string
     */
    public function getPricingSchemaFormat()
    {
        return $this->getData(self::PRICING_SCHEMA_FORMAT);
    }

    /**
     * @param $pricingSchemaFormat
     * @return $this
     */
    public function setPricingSchemaFormat($pricingSchemaFormat)
    {
        return $this->setData(self::PRICING_SCHEMA_FORMAT, $pricingSchemaFormat);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return string
     */
    public function getUses()
    {
        return $this->getData(self::USES);
    }

    /**
     * @param $uses
     * @return $this
     */
    public function setUses($uses)
    {
        return $this->setData(self::USES, $uses);
    }

    /**
     * @return string
     */
    public function getCycles()
    {
        return $this->getData(self::CYCLES);
    }

    /**
     * @param $cycles
     * @return $this
     */
    public function setCycles($cycles)
    {
        return $this->setData(self::CYCLES, $cycles);
    }

    /**
     * @return string
     */
    public function getDiscountType()
    {
        return $this->getData(self::DISCOUNT_TYPE);
    }

    /**
     * @param $discountType
     * @return $this
     */
    public function setDiscountType($discountType)
    {
        return $this->setData(self::DISCOUNT_TYPE, $discountType);
    }

    /**
     * @return float
     */
    public function getDiscountPercentage()
    {
        return $this->getData(self::DISCOUNT_PERCENTAGE);
    }

    /**
     * @param $discountPercentage
     * @return $this
     */
    public function setDiscountPercentage($discountPercentage)
    {
        return $this->setData(self::DISCOUNT_PERCENTAGE, $discountPercentage);
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
