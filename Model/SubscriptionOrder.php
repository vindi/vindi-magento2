<?php
namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\SubscriptionOrderInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class SubscriptionOrder
 * @package Vindi\Payment\Model
 */
class SubscriptionOrder extends AbstractModel implements SubscriptionOrderInterface
{
    protected function _construct()
    {
        $this->_init('Vindi\Payment\Model\ResourceModel\SubscriptionOrder');
    }

    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function setId($entityId)
    {
        $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    public function setOrderId($orderId)
    {
        $this->setData(self::ORDER_ID, $orderId);
    }

    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    public function setIncrementId($incrementId)
    {
        $this->setData(self::INCREMENT_ID, $incrementId);
    }

    public function getSubscriptionId()
    {
        return $this->getData(self::SUBSCRIPTION_ID);
    }

    public function setSubscriptionId($subscriptionId)
    {
        $this->setData(self::SUBSCRIPTION_ID, $subscriptionId);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getTotal()
    {
        return $this->getData(self::TOTAL);
    }

    public function setTotal($total)
    {
        $this->setData(self::TOTAL, $total);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
    }
}
