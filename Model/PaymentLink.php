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
}
