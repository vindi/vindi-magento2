<?php
declare(strict_types=1);

namespace Vindi\Payment\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vindi\Payment\Api\VindiPlanItemRepositoryInterface;
use Vindi\Payment\Api\Data\VindiPlanItemSearchResultInterface;
use Vindi\Payment\Api\Data\VindiPlanItemSearchResultInterfaceFactory;
use Vindi\Payment\Api\Data\VindiPlanItemInterface;
use Vindi\Payment\Api\Data\VindiPlanItemInterfaceFactory;


use Vindi\Payment\Model\ResourceModel\VindiPlanItem as ResourceModel;
use Vindi\Payment\Model\ResourceModel\VindiPlanItem\Collection;
use Vindi\Payment\Model\ResourceModel\VindiPlanItem\CollectionFactory;

/**
 * Class VindiPlanItemRepository
 * @package Vindi\Payment\Model
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class VindiPlanItemRepository implements VindiPlanItemRepositoryInterface
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
     * @var VindiPlanItemInterfaceFactory
     */
    private $vindiplanitemFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var VindiPlanItemSearchResultInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * VindiPlanItem constructor.
     * @param ResourceModel $resourceModel
     * @param VindiPlanItemInterfaceFactory $vindiplanitemFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param VindiPlanItemSearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ResourceModel $resourceModel,
        VindiPlanItemInterfaceFactory $vindiplanitemFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        VindiPlanItemSearchResultInterfaceFactory $searchResultsFactory
    ) {
        $this->resourceModel        = $resourceModel;
        $this->vindiplanitemFactory          = $vindiplanitemFactory;
        $this->collectionFactory    = $collectionFactory;
        $this->collectionProcessor  = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param int $entityId
     * @return VindiPlanItemInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): VindiPlanItemInterface
    {
        try {
            /** @var VindiPlanItemInterface $vindiplanitem */
            $vindiplanitem = $this->vindiplanitemFactory->create();
            $this->resourceModel->load($vindiplanitem, $entityId);
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Error during load vindiplanitem by Entity ID'));
        }
        return $vindiplanitem;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return VindiPlanItemSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): VindiPlanItemSearchResultInterface
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var VindiPlanItemSearchResultInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();

        $searchResult->setItems($collection->getItems())
            ->setSearchCriteria($searchCriteria)
            ->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @param VindiPlanItemInterface $vindiplanitem
     * @return void
     * @throws CouldNotSaveException
     */
    public function save(VindiPlanItemInterface $vindiplanitem): void
    {
        try {
            $this->resourceModel->save($vindiplanitem);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Error when saving vindiplanitem'));
        }
    }

    /**
     * @param VindiPlanItemInterface $vindiplanitem
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(VindiPlanItemInterface $vindiplanitem): void
    {
        try {
            $this->resourceModel->delete($vindiplanitem);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete vindiplanitem.'));
        }
    }
}
