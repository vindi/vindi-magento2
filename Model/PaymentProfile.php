<?php
namespace Vindi\Payment\Model;

use Magento\Framework\Model\AbstractModel;
use Vindi\Payment\Api\Data\PaymentProfileInterface;

class PaymentProfile extends AbstractModel implements PaymentProfileInterface
{
    protected function _construct()
    {
        $this->_init(\Vindi\Payment\Model\ResourceModel\PaymentProfile::class);
    }

    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getPaymentProfileId()
    {
        return $this->getData(self::PAYMENT_PROFILE_ID);
    }

    public function setPaymentProfileId($paymentProfileId)
    {
        return $this->setData(self::PAYMENT_PROFILE_ID, $paymentProfileId);
    }

    public function getVindiCustomerId()
    {
        return $this->getData(self::VINDI_CUSTOMER_ID);
    }

    public function setVindiCustomerId($vindiCustomerId)
    {
        return $this->setData(self::VINDI_CUSTOMER_ID, $vindiCustomerId);
    }

    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    public function getCustomerEmail()
    {
        return $this->getData(self::CUSTOMER_EMAIL);
    }

    public function setCustomerEmail($customerEmail)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $customerEmail);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getToken()
    {
        return $this->getData(self::TOKEN);
    }

    public function setToken($token)
    {
        return $this->setData(self::TOKEN, $token);
    }

    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    public function getCcType()
    {
        return $this->getData(self::CC_TYPE);
    }

    public function setCcType($ccType)
    {
        return $this->setData(self::CC_TYPE, $ccType);
    }

    public function getCcLast4()
    {
        return $this->getData(self::CC_LAST_4);
    }

    public function setCcLast4($ccLast4)
    {
        return $this->setData(self::CC_LAST_4, $ccLast4);
    }

    public function getCcName()
    {
        return $this->getData(self::CC_NAME);
    }

    public function setCcName($ccName)
    {
        return $this->setData(self::CC_NAME, $ccName);
    }

    public function getCcExpDate()
    {
        return $this->getData(self::CC_EXP_DATE);
    }

    public function setCcExpDate($ccExpDate)
    {
        return $this->setData(self::CC_EXP_DATE, $ccExpDate);
    }

    public function getCcNumber()
    {
        return $this->getData(self::CC_NUMBER);
    }

    public function setCcNumber($ccNumber)
    {
        return $this->setData(self::CC_NUMBER, $ccNumber);
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
