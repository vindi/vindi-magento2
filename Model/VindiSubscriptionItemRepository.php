<?php
namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\VindiPlanSearchResultInterface;
use Vindi\Payment\Api\Data\VindiSubscriptionItemInterface;
use Vindi\Payment\Api\Data\VindiSubscriptionItemInterfaceFactory;
use Vindi\Payment\Api\Data\VindiSubscriptionItemSearchResultInterfaceFactory;
use Vindi\Payment\Api\Data\VindiSubscriptionItemSearchResultInterface;
use Vindi\Payment\Api\VindiSubscriptionItemRepositoryInterface;
use Vindi\Payment\Model\ResourceModel\VindiPlan\Collection;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem as ResourceModel;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class VindiSubscriptionItemRepository
 * @package Vindi\Payment\Model
 */
class VindiSubscriptionItemRepository implements VindiSubscriptionItemRepositoryInterface
{
    /**
     * @var ResourceModel
     */
    private $resourceModel;

    /**
     * @var VindiSubscriptionItemInterfaceFactory
     */
    private $vindiSubscriptionItemFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var VindiSubscriptionItemSearchResultInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * VindiSubscriptionItemRepository constructor.
     * @param ResourceModel $resourceModel
     * @param VindiSubscriptionItemInterfaceFactory $vindiSubscriptionItemFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param VindiSubscriptionItemSearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ResourceModel $resourceModel,
        VindiSubscriptionItemInterfaceFactory $vindiSubscriptionItemFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        VindiSubscriptionItemSearchResultInterfaceFactory $searchResultsFactory
    ) {
        $this->resourceModel = $resourceModel;
        $this->vindiSubscriptionItemFactory = $vindiSubscriptionItemFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param int $entityId
     * @return VindiSubscriptionItemInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): VindiSubscriptionItemInterface
    {
        try {
            $vindiSubscriptionItem = $this->vindiSubscriptionItemFactory->create();
            $this->resourceModel->load($vindiSubscriptionItem, $entityId);
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Unable to find subscription item with ID: %1', $entityId));
        }
        return $vindiSubscriptionItem;
    }

    /**
     * @param VindiSubscriptionItemInterface $vindiSubscriptionItem
     * @return void
     * @throws CouldNotSaveException
     */
    public function save(VindiSubscriptionItemInterface $vindiSubscriptionItem): void
    {
        try {
            $this->resourceModel->save($vindiSubscriptionItem);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save subscription item.'));
        }
    }

    /**
     * @param VindiSubscriptionItemInterface $vindiSubscriptionItem
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(VindiSubscriptionItemInterface $vindiSubscriptionItem): void
    {
        try {
            $this->resourceModel->delete($vindiSubscriptionItem);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete subscription item.'));
        }
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return VindiSubscriptionItemSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): VindiSubscriptionItemSearchResultInterface
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var VindiSubscriptionItemSearchResultInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();

        $searchResult->setItems($collection->getItems())
            ->setSearchCriteria($searchCriteria)
            ->setTotalCount($collection->getSize());

        return $searchResult;
    }
}
