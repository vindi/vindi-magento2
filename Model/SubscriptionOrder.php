<?php

namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\SubscriptionOrderInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * SubscriptionOrder model for handling subscription order data.
 */
class SubscriptionOrder extends AbstractModel implements SubscriptionOrderInterface
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('Vindi\Payment\Model\ResourceModel\SubscriptionOrder');
    }

    /**
     * Get entity ID
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set entity ID
     * @param int $entityId
     * @return $this
     */
    public function setId($entityId)
    {
        $this->setData(self::ENTITY_ID, $entityId);
        return $this;
    }

    /**
     * Get order ID
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set order ID
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        $this->setData(self::ORDER_ID, $orderId);
        return $this;
    }

    /**
     * Get increment ID
     * @return string|null
     */
    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    /**
     * Set increment ID
     * @param string $incrementId
     * @return $this
     */
    public function setIncrementId($incrementId)
    {
        $this->setData(self::INCREMENT_ID, $incrementId);
        return $this;
    }

    /**
     * Get subscription ID
     * @return int|null
     */
    public function getSubscriptionId()
    {
        return $this->getData(self::SUBSCRIPTION_ID);
    }

    /**
     * Set subscription ID
     * @param int $subscriptionId
     * @return $this
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->setData(self::SUBSCRIPTION_ID, $subscriptionId);
        return $this;
    }

    /**
     * Get creation date
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set creation date
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
        return $this;
    }

    /**
     * Get total
     * @return float|null
     */
    public function getTotal()
    {
        return $this->getData(self::TOTAL);
    }

    /**
     * Set total
     * @param float $total
     * @return $this
     */
    public function setTotal($total)
    {
        $this->setData(self::TOTAL, $total);
        return $this;
    }

    /**
     * Get status
     * @return string|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }
}
