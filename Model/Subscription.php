<?php

namespace Vindi\Payment\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Vindi\Payment\Api\Data\SubscriptionInterface;
use Vindi\Payment\Api\Data\SubscriptionInterfaceFactory;
use Vindi\Payment\Model\ResourceModel\Subscription\Collection;

/**
 * Class Subscription
 * @package Vindi\Payment\Model
 */
class Subscription extends AbstractModel
{
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var SubscriptionInterfaceFactory
     */
    protected $subscriptionDataFactory;

    protected $_eventPrefix = 'vindi_payment_subscription';

    /**
     * Subscription constructor.
     * @param Context $context
     * @param Registry $registry
     * @param SubscriptionInterfaceFactory $subscriptionDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ResourceModel\Subscription $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SubscriptionInterfaceFactory $subscriptionDataFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceModel\Subscription $resource,
        Collection $resourceCollection,
        array $data = []
    ) {
        $this->subscriptionDataFactory = $subscriptionDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return SubscriptionInterface
     */
    public function getDataModel()
    {
        $subscriptionData = $this->getData();
        
        $subscriptionDataObject = $this->subscriptionDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $subscriptionDataObject,
            $subscriptionData,
            SubscriptionInterface::class
        );
        
        return $subscriptionDataObject;
    }
}
