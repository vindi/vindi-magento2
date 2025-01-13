<?php

namespace Vindi\Payment\Model;

use Magento\Framework\Model\AbstractModel;
use Vindi\Payment\Api\Data\VindiSubscriptionItemDiscountInterface;

class VindiSubscriptionItemDiscount extends AbstractModel implements VindiSubscriptionItemDiscountInterface
{
    protected function _construct()
    {
        $this->_init(\Vindi\Payment\Model\ResourceModel\VindiSubscriptionItemDiscount::class);
    }

    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    public function getVindiDiscountId()
    {
        return $this->getData(self::VINDI_DISCOUNT_ID);
    }

    public function setVindiDiscountId($vindiDiscountId)
    {
        return $this->setData(self::VINDI_DISCOUNT_ID, $vindiDiscountId);
    }
    public function getSubscriptionId()
    {
        return $this->getData(self::SUBSCRIPTION_ID);
    }

    public function setSubscriptionId($subscriptionId)
    {
        return $this->setData(self::SUBSCRIPTION_ID, $subscriptionId);
    }

    public function getProductItemId()
    {
        return $this->getData(self::PRODUCT_ITEM_ID);
    }

    public function setProductItemId($productItemId)
    {
        return $this->setData(self::PRODUCT_ITEM_ID, $productItemId);
    }

    public function getProductName()
    {
        return $this->getData(self::PRODUCT_NAME);
    }

    public function setProductName($productName)
    {
        return $this->setData(self::PRODUCT_NAME, $productName);
    }

    public function getMagentoProductId()
    {
        return $this->getData(self::MAGENTO_PRODUCT_ID);
    }

    public function setMagentoProductId($magentoProductId)
    {
        return $this->setData(self::MAGENTO_PRODUCT_ID, $magentoProductId);
    }

    public function getMagentoProductSku()
    {
        return $this->getData(self::MAGENTO_PRODUCT_SKU);
    }

    public function setMagentoProductSku($magentoProductSku)
    {
        return $this->setData(self::MAGENTO_PRODUCT_SKU, $magentoProductSku);
    }

    public function getDiscountType()
    {
        return $this->getData(self::DISCOUNT_TYPE);
    }

    public function setDiscountType($discountType)
    {
        return $this->setData(self::DISCOUNT_TYPE, $discountType);
    }

    public function getPercentage()
    {
        return $this->getData(self::PERCENTAGE);
    }

    public function setPercentage($percentage)
    {
        return $this->setData(self::PERCENTAGE, $percentage);
    }

    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    public function getQuantity()
    {
        return $this->getData(self::QUANTITY);
    }

    public function setQuantity($quantity)
    {
        return $this->setData(self::QUANTITY, $quantity);
    }

    public function getCycles()
    {
        return $this->getData(self::CYCLES);
    }

    public function setCycles($cycles)
    {
        return $this->setData(self::CYCLES, $cycles);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
