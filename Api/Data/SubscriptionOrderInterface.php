<?php
declare(strict_types=1);

namespace Vindi\Payment\Api\Data;

/**
 * Interface SubscriptionOrderInterface
 * @package Vindi\Payment\Api\Data
 */
interface SubscriptionOrderInterface
{
    const ENTITY_ID = 'entity_id';
    const ORDER_ID = 'order_id';
    const INCREMENT_ID = 'increment_id';
    const SUBSCRIPTION_ID = 'subscription_id';
    const CREATED_AT = 'created_at';
    const TOTAL = 'total';
    const STATUS = 'status';

    public function getId();
    public function setId($entityId);
    public function getOrderId();
    public function setOrderId($orderId);
    public function getIncrementId();
    public function setIncrementId($incrementId);
    public function getSubscriptionId();
    public function setSubscriptionId($subscriptionId);
    public function getCreatedAt();
    public function setCreatedAt($createdAt);
    public function getTotal();
    public function setTotal($total);
    public function getStatus();
    public function setStatus($status);

}
