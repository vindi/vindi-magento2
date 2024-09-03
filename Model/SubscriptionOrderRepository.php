<?php
declare(strict_types=1);

namespace Vindi\Payment\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vindi\Payment\Api\SubscriptionOrderRepositoryInterface;
use Vindi\Payment\Api\Data\SubscriptionOrderSearchResultInterface;
use Vindi\Payment\Api\Data\SubscriptionOrderSearchResultInterfaceFactory;
use Vindi\Payment\Api\Data\SubscriptionOrderInterface;
use Vindi\Payment\Api\Data\SubscriptionOrderInterfaceFactory;
use Vindi\Payment\Model\ResourceModel\SubscriptionOrder as ResourceModel;
use Vindi\Payment\Model\ResourceModel\SubscriptionOrder\Collection;
use Vindi\Payment\Model\ResourceModel\SubscriptionOrder\CollectionFactory;

/**
 * Class SubscriptionOrderRepository
 * @package Vindi\Payment\Model
 */
class SubscriptionOrderRepository implements SubscriptionOrderRepositoryInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ResourceModel
     */
    private $resourceModel;

    /**
     * @var SubscriptionOrderInterfaceFactory
     */
    private $subscriptionOrderFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var SubscriptionOrderSearchResultInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * SubscriptionOrder constructor.
     * @param ResourceModel $resourceModel
     * @param SubscriptionOrderInterfaceFactory $subscriptionOrderFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SubscriptionOrderSearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ResourceModel $resourceModel,
        SubscriptionOrderInterfaceFactory $subscriptionOrderFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        SubscriptionOrderSearchResultInterfaceFactory $searchResultsFactory
    ) {
        $this->resourceModel = $resourceModel;
        $this->subscriptionOrderFactory = $subscriptionOrderFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * Retrieve subscription order by entity ID
     *
     * @param int $entityId
     * @return SubscriptionOrderInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): SubscriptionOrderInterface
    {
        try {
            /** @var SubscriptionOrderInterface $subscriptionOrder */
            $subscriptionOrder = $this->subscriptionOrderFactory->create();
            $this->resourceModel->load($subscriptionOrder, $entityId);
            if (!$subscriptionOrder->getId()) {
                throw new NoSuchEntityException(__('No subscription order found for the given entity ID.'));
            }
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Error during load subscription order by entity ID'));
        }
        return $subscriptionOrder;
    }

    /**
     * Retrieve subscription order by subscription ID
     *
     * @param int $subscriptionId
     * @return SubscriptionOrderInterface
     * @throws NoSuchEntityException
     */
    public function getBySubscriptionId(int $subscriptionId): SubscriptionOrderInterface
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('subscription_id', $subscriptionId);

        /** @var SubscriptionOrderInterface $subscriptionOrder */
        $subscriptionOrder = $collection->getFirstItem();

        if (!$subscriptionOrder || !$subscriptionOrder->getId()) {
            throw new NoSuchEntityException(__('No subscription order found for the given subscription ID.'));
        }

        return $subscriptionOrder;
    }

    /**
     * Retrieve a list of subscription orders by subscription ID
     *
     * @param int $subscriptionId
     * @return SubscriptionOrderInterface[]
     */
    public function getListBySubscriptionId(int $subscriptionId): array
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('subscription_id', $subscriptionId);

        return $collection->getItems();
    }

    /**
     * Retrieve a list of subscription orders matching the search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SubscriptionOrderSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SubscriptionOrderSearchResultInterface
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var SubscriptionOrderSearchResultInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();

        $searchResult->setItems($collection->getItems())
            ->setSearchCriteria($searchCriteria)
            ->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * Save a subscription order
     *
     * @param SubscriptionOrderInterface $subscriptionOrder
     * @return void
     * @throws CouldNotSaveException
     */
    public function save(SubscriptionOrderInterface $subscriptionOrder): void
    {
        try {
            $this->resourceModel->save($subscriptionOrder);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Error when saving subscription order'));
        }
    }

    /**
     * Delete a subscription order
     *
     * @param SubscriptionOrderInterface $subscriptionOrder
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(SubscriptionOrderInterface $subscriptionOrder): void
    {
        try {
            $this->resourceModel->delete($subscriptionOrder);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete subscription order.'));
        }
    }
}
