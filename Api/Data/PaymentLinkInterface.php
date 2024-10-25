<?php

declare(strict_types=1);

/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Vindi
 * @package     Vindi_Payment
 */

namespace Vindi\Payment\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface PaymentLinkInterface extends ExtensibleDataInterface
{
    const ENTITY_ID = 'entity_id';
    const LINK = 'link';
    const ORDER_ID = 'order_id';
    const VINDI_PAYMENT_METHOD = 'vindi_payment_method';
    const CUSTOMER_ID = 'customer_id';
    const CREATED_AT = 'created_at';
    const STATUS = 'status';

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $id
     */
    public function setEntityId(int $entityId);

    /**
     * @return string
     */
    public function getLink();

    /**
     * @param string $link
     */
    public function setLink(string $link);

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $orderId
     */
    public function setOrderId(int $orderId);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     */
    public function setCreatedAt(string $createdAt);

    /**
     * @return string
     */
    public function getVindiPaymentMethod();

    /**
     * @param string $vindiPaymentMethod
     */
    public function setVindiPaymentMethod(string $vindiPaymentMethod);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     */
    public function setCustomerId(int $customerId);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     */
    public function setStatus(string $status);
}
