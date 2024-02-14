<?php
namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\VindiPlanInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class VindiPlan
 * @package Vindi\Payment\Model
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class VindiPlan extends AbstractModel implements VindiPlanInterface
{
    protected function _construct()
    {
        $this->_init(\Vindi\Payment\Model\ResourceModel\VindiPlan::class);
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array|int|mixed|null
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @param $entityId
     * @return VindiPlan|void
     */
    public function setId($entityId)
    {
        $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @return array|int|mixed|null
     */
    public function getVindiId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @param $vindiId
     * @return VindiPlan|void
     */
    public function setVindiId($vindiId)
    {
        $this->setData(self::VINDI_ID, $vindiId);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @param $name
     * @return VindiPlan|void
     */
    public function setName($name)
    {
        $this->setData(self::NAME, $name);
    }

    /**
     * @return array|int|mixed|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param $status
     * @return VindiPlan|void
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
    }

    public function getInterval()
    {
        return $this->getData(self::INTERVAL);
    }

    public function setInterval($interval)
    {
        return $this->setData(self::INTERVAL, $interval);
    }

    public function getIntervalCount()
    {
        return $this->getData(self::INTERVAL_COUNT);
    }

    public function setIntervalCount($intervalCount)
    {
        return $this->setData(self::INTERVAL_COUNT, $intervalCount);
    }

    public function getBillingTriggerType()
    {
        return $this->getData(self::BILLING_TRIGGER_TYPE);
    }

    public function setBillingTriggerType($billingTriggerType)
    {
        return $this->setData(self::BILLING_TRIGGER_TYPE, $billingTriggerType);
    }

    public function getBillingTriggerDay()
    {
        return $this->getData(self::BILLING_TRIGGER_DAY);
    }

    public function setBillingTriggerDay($billingTriggerDay)
    {
        return $this->setData(self::BILLING_TRIGGER_DAY, $billingTriggerDay);
    }

    public function getBillingCycles()
    {
        return $this->getData(self::BILLING_CYCLES);
    }

    public function setBillingCycles($billingCycles)
    {
        return $this->setData(self::BILLING_CYCLES, $billingCycles);
    }

    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    public function getInstallments()
    {
        return $this->getData(self::INSTALLMENTS);
    }

    public function setInstallments($installments)
    {
        return $this->setData(self::INSTALLMENTS, $installments);
    }

    public function getInvoiceSplit()
    {
        return $this->getData(self::INVOICE_SPLIT);
    }

    public function setInvoiceSplit($invoiceSplit)
    {
        return $this->setData(self::INVOICE_SPLIT, $invoiceSplit);
    }

    public function getMetadata()
    {
        return $this->getData(self::METADATA);
    }

    public function setMetadata($metadata)
    {
        return $this->setData(self::METADATA, $metadata);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param $createAt
     * @return VindiPlan|void
     */
    public function setCreatedAt($createAt)
    {
        $this->setData(self::CREATED_AT, $createAt);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param $updatedAt
     * @return VindiPlan|void
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
