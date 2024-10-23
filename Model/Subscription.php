<?php

namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\SubscriptionInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Vindi\Payment\Model\ResourceModel\Subscription as SubscriptionResource;
use Vindi\Payment\Model\ResourceModel\Subscription\Collection;
use Vindi\Payment\Model\SubscriptionFactory;

/**
 * Class Subscription
 * @package Vindi\Payment\Model
 */
class Subscription extends AbstractModel implements SubscriptionInterface, IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'vindi_payment_subscription';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = 'vindi_payment_subscription';

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var SubscriptionFactory
     */
    protected $subscriptionFactory;

    /**
     * Subscription constructor.
     * @param Context $context
     * @param Registry $registry
     * @param SubscriptionFactory $subscriptionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param SubscriptionResource $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SubscriptionFactory $subscriptionFactory,
        DataObjectHelper $dataObjectHelper,
        SubscriptionResource $resource,
        Collection $resourceCollection,
        array $data = []
    ) {
        $this->subscriptionFactory = $subscriptionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(SubscriptionResource::class);
    }

    /**
     * Get identities
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set ID
     *
     * @param string $id
     * @return SubscriptionInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get client
     *
     * @return string|null
     */
    public function getClient()
    {
        return $this->getData(self::CLIENT);
    }

    /**
     * Set client
     *
     * @param string $client
     * @return SubscriptionInterface
     */
    public function setClient($client)
    {
        return $this->setData(self::CLIENT, $client);
    }

    /**
     * Get plan
     *
     * @return string|null
     */
    public function getPlan()
    {
        return $this->getData(self::PLAN);
    }

    /**
     * Set plan
     *
     * @param string $plan
     * @return SubscriptionInterface
     */
    public function setPlan($plan)
    {
        return $this->setData(self::PLAN, $plan);
    }

    /**
     * Get start_at
     *
     * @return string|null
     */
    public function getStartAt()
    {
        return $this->getData(self::START_AT);
    }

    /**
     * Set start_at
     *
     * @param string $startAt
     * @return SubscriptionInterface
     */
    public function setStartAt($startAt)
    {
        return $this->setData(self::START_AT, $startAt);
    }

    /**
     * Get payment_method
     *
     * @return string|null
     */
    public function getPaymentMethod()
    {
        return $this->getData(self::PAYMENT_METHOD);
    }

    /**
     * Set payment_method
     *
     * @param string $paymentMethod
     * @return SubscriptionInterface
     */
    public function setPaymentMethod($paymentMethod)
    {
        return $this->setData(self::PAYMENT_METHOD, $paymentMethod);
    }

    /**
     * Get payment_profile
     *
     * @return string|null
     */
    public function getPaymentProfile()
    {
        return $this->getData(self::PAYMENT_PROFILE);
    }

    /**
     * Set payment_profile
     *
     * @param string $paymentProfile
     * @return SubscriptionInterface
     */
    public function setPaymentProfile($paymentProfile)
    {
        return $this->setData(self::PAYMENT_PROFILE, $paymentProfile);
    }

    /**
     * Get status
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     *
     * @param string $status
     * @return SubscriptionInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get next_billing_at
     *
     * @return string|null
     */
    public function getNextBillingAt()
    {
        return $this->getData(self::NEXT_BILLING_AT);
    }

    /**
     * Set next_billing_at
     *
     * @param string $nextBillingAt
     * @return SubscriptionInterface
     */
    public function setNextBillingAt($nextBillingAt)
    {
        return $this->setData(self::NEXT_BILLING_AT, $nextBillingAt);
    }

    /**
     * Get bill_id
     *
     * @return string|null
     */
    public function getBillId()
    {
        return $this->getData(self::BILL_ID);
    }

    /**
     * Set bill_id
     *
     * @param string $billId
     * @return SubscriptionInterface
     */
    public function setBillId($billId)
    {
        return $this->setData(self::BILL_ID, $billId);
    }

    /**
     * Get response_data
     *
     * @return string|null
     */
    public function getResponseData()
    {
        return $this->getData(self::RESPONSE_DATA);
    }

    /**
     * Set response_data
     *
     * @param string $responseData
     * @return SubscriptionInterface
     */
    public function setResponseData($responseData)
    {
        return $this->setData(self::RESPONSE_DATA, $responseData);
    }

    /**
     * @return SubscriptionInterface
     */
    public function getDataModel()
    {
        $subscriptionData = $this->getData();

        $subscriptionDataObject = $this->subscriptionFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $subscriptionDataObject,
            $subscriptionData,
            SubscriptionInterface::class
        );

        return $subscriptionDataObject;
    }
}
