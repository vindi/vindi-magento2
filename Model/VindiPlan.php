<?php
namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\VindiPlanInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class VindiPlan
 * @package Vindi\Payment\Model

 */
class VindiPlan extends AbstractModel implements VindiPlanInterface
{
    const CACHE_TAG = 'vindi_payment_vindiplan';

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
        return $this->getData(self::VINDI_ID);
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

    /**
     * @return array|mixed|string|null
     */
    public function getInterval()
    {
        return $this->getData(self::INTERVAL);
    }

    /**
     * @param $interval
     * @return VindiPlan|void
     */
    public function setInterval($interval)
    {
        return $this->setData(self::INTERVAL, $interval);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getIntervalCount()
    {
        return $this->getData(self::INTERVAL_COUNT);
    }

    /**
     * @param $intervalCount
     * @return VindiPlan|void
     */
    public function setIntervalCount($intervalCount)
    {
        return $this->setData(self::INTERVAL_COUNT, $intervalCount);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getBillingTriggerType()
    {
        return $this->getData(self::BILLING_TRIGGER_TYPE);
    }

    /**
     * @param $billingTriggerType
     * @return VindiPlan|void
     */
    public function setBillingTriggerType($billingTriggerType)
    {
        return $this->setData(self::BILLING_TRIGGER_TYPE, $billingTriggerType);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getBillingTriggerDay()
    {
        return $this->getData(self::BILLING_TRIGGER_DAY);
    }

    /**
     * @param $billingTriggerDay
     * @return VindiPlan|void
     */
    public function setBillingTriggerDay($billingTriggerDay)
    {
        return $this->setData(self::BILLING_TRIGGER_DAY, $billingTriggerDay);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getBillingCycles()
    {
        return $this->getData(self::BILLING_CYCLES);
    }

    /**
     * @param $billingCycles
     * @return VindiPlan|void
     */
    public function setBillingCycles($billingCycles)
    {
        return $this->setData(self::BILLING_CYCLES, $billingCycles);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * @param $code
     * @return VindiPlan|void
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @param $description
     * @return VindiPlan|void
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getDescriptionDisplayOnProductPage()
    {
        return $this->getData(self::DESCRIPTION_DISPLAY_ON_PRODUCT_PAGE);
    }

    /**
     * @param $descriptionDisplayOnProductPage
     * @return VindiPlan|void
     */
    public function setDescriptionDisplayOnProductPage($descriptionDisplayOnProductPage)
    {
        return $this->setData(self::DESCRIPTION_DISPLAY_ON_PRODUCT_PAGE, $descriptionDisplayOnProductPage);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getInstallments()
    {
        return $this->getData(self::INSTALLMENTS);
    }

    /**
     * @param $installments
     * @return VindiPlan|void
     */
    public function setInstallments($installments)
    {
        return $this->setData(self::INSTALLMENTS, $installments);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getInvoiceSplit()
    {
        return $this->getData(self::INVOICE_SPLIT);
    }

    /**
     * @param $invoiceSplit
     * @return VindiPlan|void
     */
    public function setInvoiceSplit($invoiceSplit)
    {
        return $this->setData(self::INVOICE_SPLIT, $invoiceSplit);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getMetadata()
    {
        return $this->getData(self::METADATA);
    }

    /**
     * @param $metadata
     * @return VindiPlan|void
     */
    public function setMetadata($metadata)
    {
        return $this->setData(self::METADATA, $metadata);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getDuration()
    {
        return $this->getData(self::DURATION);
    }

    /**
     * @param $duration
     * @return VindiPlan|void
     */
    public function setDuration($duration)
    {
        return $this->setData(self::DURATION, $duration);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getBillingTriggerDayTypeOnPeriod()
    {
        return $this->getData(self::BILLING_TRIGGER_DAY_TYPE_ON_PERIOD);
    }

    /**
     * @param $billingTriggerDayTypeOnPeriod
     * @return VindiPlan|void
     */
    public function setBillingTriggerDayTypeOnPeriod($billingTriggerDayTypeOnPeriod)
    {
        return $this->setData(self::BILLING_TRIGGER_DAY_TYPE_ON_PERIOD, $billingTriggerDayTypeOnPeriod);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getBillingTriggerDayBasedOnPeriod()
    {
        return $this->getData(self::BILLING_TRIGGER_DAY_BASED_ON_PERIOD);
    }

    /**
     * @param $billingTriggerDayBasedOnPeriod
     * @return VindiPlan|void
     */
    public function setBillingTriggerDayBasedOnPeriod($billingTriggerDayBasedOnPeriod)
    {
        return $this->setData(self::BILLING_TRIGGER_DAY_BASED_ON_PERIOD, $billingTriggerDayBasedOnPeriod);
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
