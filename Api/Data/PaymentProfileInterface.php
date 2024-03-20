<?php
declare(strict_types=1);

namespace Vindi\Payment\Api\Data;

/**
 * Interface PaymentProfileInterface
 * @package Vindi\Payment\Api\Data
 */
interface PaymentProfileInterface
{
    const ENTITY_ID = 'entity_id';
    const PAYMENT_PROFILE_ID = 'payment_profile_id';
    const VINDI_CUSTOMER_ID = 'vindi_customer_id';
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_EMAIL = 'customer_email';
    const STATUS = 'status';
    const TOKEN = 'token';
    const TYPE = 'type';

    const CC_TYPE = 'cc_type';
    const CC_LAST_4 = 'cc_last_4';
    const CC_NAME = 'cc_name';
    const CC_EXP_DATE = 'cc_exp_date';
    const CC_NUMBER = 'cc_number';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function getEntityId();
    public function setEntityId($entityId);

    public function getPaymentProfileId();
    public function setPaymentProfileId($paymentProfileId);

    public function getVindiCustomerId();
    public function setVindiCustomerId($vindiCustomerId);

    public function getCustomerId();
    public function setCustomerId($customerId);

    public function getCustomerEmail();
    public function setCustomerEmail($customerEmail);

    public function getStatus();
    public function setStatus($status);

    public function getToken();
    public function setToken($token);

    public function getType();
    public function setType($type);
    public function getCcType();
    public function setCcType($ccType);
    public function getCcLast4();
    public function setCcLast4($ccLast4);

    public function getCcName();
    public function setCcName($ccName);

    public function getCcExpDate();
    public function setCcExpDate($ccExpDate);

    public function getCcNumber();
    public function setCcNumber($ccNumber);

    public function getCreatedAt();
    public function setCreatedAt($createdAt);

    public function getUpdatedAt();
    public function setUpdatedAt($updatedAt);
}
