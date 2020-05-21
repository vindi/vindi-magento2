<?php

namespace Vindi\Payment\Model;

use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Vindi\Payment\Api\Data\SubscriptionInterface;
use Vindi\Payment\Api\Data\SubscriptionSearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\DataObjectHelper;
use Vindi\Payment\Api\SubscriptionRepositoryInterface;
use Vindi\Payment\Model\ResourceModel\Subscription\CollectionFactory as SubscriptionCollectionFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Vindi\Payment\Model\ResourceModel\Subscription as ResourceSubscription;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Vindi\Payment\Api\Data\SubscriptionInterfaceFactory;

/**
 * Class SubscriptionRepository
 * @package Vindi\Payment\Model
 */
class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    /**
     * @var SubscriptionInterfaceFactory
     */
    protected $dataSubscriptionFactory;
    /**
     * @var SubscriptionSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;
    /**
     * @var SubscriptionFactory
     */
    protected $subscriptionFactory;
    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * @var ResourceSubscription
     */
    protected $resource;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;
    /**
     * @var SubscriptionCollectionFactory
     */
    protected $subscriptionCollectionFactory;

    /**
     * SubscriptionRepository constructor.
     * @param ResourceSubscription $resource
     * @param SubscriptionFactory $subscriptionFactory
     * @param SubscriptionInterfaceFactory $dataSubscriptionFactory
     * @param SubscriptionCollectionFactory $subscriptionCollectionFactory
     * @param SubscriptionSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceSubscription $resource,
        SubscriptionFactory $subscriptionFactory,
        SubscriptionInterfaceFactory $dataSubscriptionFactory,
        SubscriptionCollectionFactory $subscriptionCollectionFactory,
        SubscriptionSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataSubscriptionFactory = $dataSubscriptionFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        SubscriptionInterface $subscription
    ) {
        $subscriptionData = $this->extensibleDataObjectConverter->toNestedArray(
            $subscription,
            [],
            SubscriptionInterface::class
        );
        
        $subscriptionModel = $this->subscriptionFactory->create()->setData($subscriptionData);
        
        try {
            $this->resource->save($subscriptionModel);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the subscription: %1',
                $exception->getMessage()
            ));
        }
        return $subscriptionModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($subscriptionId)
    {
        $subscription = $this->subscriptionFactory->create();
        $this->resource->load($subscription, $subscriptionId);
        if (!$subscription->getId()) {
            throw new NoSuchEntityException(__('Subscription with id "%1" does not exist.', $subscriptionId));
        }
        return $subscription->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        SearchCriteriaInterface $criteria
    ) {
        $collection = $this->subscriptionCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            SubscriptionInterface::class
        );
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
