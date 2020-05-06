<?php

namespace Vindi\Payment\Api\Data;

/**
 * Interface SubscriptionInterface
 * @package Vindi\Payment\Api\Data
 */
interface SubscriptionInterface
{
    const ID = 'id';
    const PAYMENT_METHOD = 'payment_method';
    const PAYMENT_PROFILE = 'payment_profile';
    const CLIENT = 'client';
    const STATUS = 'status';
    const START_AT = 'start_at';
    const PLAN = 'plan';

    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     * @param string $id
     * @return \Vindi\Payment\Api\Data\SubscriptionInterface
     */
    public function setId($id);

    /**
     * Get client
     * @return string|null
     */
    public function getClient();

    /**
     * Set client
     * @param string $client
     * @return \Vindi\Payment\Api\Data\SubscriptionInterface
     */
    public function setClient($client);

    /**
     * Get plan
     * @return string|null
     */
    public function getPlan();

    /**
     * Set plan
     * @param string $plan
     * @return \Vindi\Payment\Api\Data\SubscriptionInterface
     */
    public function setPlan($plan);

    /**
     * Get start_at
     * @return string|null
     */
    public function getStartAt();

    /**
     * Set start_at
     * @param string $startAt
     * @return \Vindi\Payment\Api\Data\SubscriptionInterface
     */
    public function setStartAt($startAt);

    /**
     * Get payment_method
     * @return string|null
     */
    public function getPaymentMethod();

    /**
     * Set payment_method
     * @param string $paymentMethod
     * @return \Vindi\Payment\Api\Data\SubscriptionInterface
     */
    public function setPaymentMethod($paymentMethod);

    /**
     * Get payment_profile
     * @return string|null
     */
    public function getPaymentProfile();

    /**
     * Set payment_profile
     * @param string $paymentProfile
     * @return \Vindi\Payment\Api\Data\SubscriptionInterface
     */
    public function setPaymentProfile($paymentProfile);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Vindi\Payment\Api\Data\SubscriptionInterface
     */
    public function setStatus($status);
}
