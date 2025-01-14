<?php

declare(strict_types=1);

namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\PaymentLinkInterface;
use Magento\Framework\Model\AbstractModel;

class PaymentLink extends AbstractModel implements PaymentLinkInterface
{
    const CACHE_TAG = 'vindi_vr_payment_link';

    /**
     * @var string
     */
    protected $_cacheTag = 'vindi_vr_payment_link';

    /**
     * @var string
     */
    protected $_eventPrefix = 'vindi_vr_payment_link';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\PaymentLink::class);
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($entityId)
    {
        $this->setData(self::ENTITY_ID, $entityId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLink()
    {
        return $this->getData(self::LINK);
    }

    /**
     * @inheritdoc
     */
    public function setLink(string $link)
    {
        $this->setData(self::LINK, $link);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setOrderId(int $orderId)
    {
        $this->setData(self::ORDER_ID, $orderId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVindiPaymentMethod()
    {
        return $this->getData(self::VINDI_PAYMENT_METHOD);
    }

    /**
     * @inheritdoc
     */
    public function setVindiPaymentMethod($vindiPaymentMethod)
    {
        $this->setData(self::VINDI_PAYMENT_METHOD, $vindiPaymentMethod);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId(int $customerId)
    {
        $this->setData(self::CUSTOMER_ID, $customerId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus(string $status)
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExpiredAt()
    {
        return $this->getData(self::EXPIRED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setExpiredAt($expiredAt)
    {
        $this->setData(self::EXPIRED_AT, $expiredAt);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSuccessPageAccessed()
    {
        return $this->getData(self::SUCCESS_PAGE_ACCESSED);
    }

    /**
     * @inheritdoc
     */
    public function setSuccessPageAccessed(bool $successPageAccessed)
    {
        $this->setData(self::SUCCESS_PAGE_ACCESSED, $successPageAccessed);
        return $this;
    }
}

