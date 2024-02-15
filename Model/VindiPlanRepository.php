<?php
declare(strict_types=1);

namespace Vindi\Payment\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vindi\Payment\Api\VindiPlanRepositoryInterface;
use Vindi\Payment\Api\Data\VindiPlanSearchResultInterface;
use Vindi\Payment\Api\Data\VindiPlanSearchResultInterfaceFactory;
use Vindi\Payment\Api\Data\VindiPlanInterface;
use Vindi\Payment\Api\Data\VindiPlanInterfaceFactory;


use Vindi\Payment\Model\ResourceModel\VindiPlan as ResourceModel;
use Vindi\Payment\Model\ResourceModel\VindiPlan\Collection;
use Vindi\Payment\Model\ResourceModel\VindiPlan\CollectionFactory;

/**
 * Class VindiPlanRepository
 * @package Vindi\Payment\Model
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class VindiPlanRepository implements VindiPlanRepositoryInterface
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
     * @var VindiPlanInterfaceFactory
     */
    private $vindiplanFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var VindiPlanSearchResultInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * VindiPlan constructor.
     * @param ResourceModel $resourceModel
     * @param VindiPlanInterfaceFactory $vindiplanFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param VindiPlanSearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ResourceModel $resourceModel,
        VindiPlanInterfaceFactory $vindiplanFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        VindiPlanSearchResultInterfaceFactory $searchResultsFactory
    ) {
        $this->resourceModel        = $resourceModel;
        $this->vindiplanFactory          = $vindiplanFactory;
        $this->collectionFactory    = $collectionFactory;
        $this->collectionProcessor  = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param int $entityId
     * @return VindiPlanInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): VindiPlanInterface
    {
        try {
            /** @var VindiPlanInterface $vindiplan */
            $vindiplan = $this->vindiplanFactory->create();
            $this->resourceModel->load($vindiplan, $entityId);
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Error during load vindiplan by Entity ID'));
        }
        return $vindiplan;
    }

    /**
     * Retrieve plan by code.
     *
     * @param string $code
     * @return VindiPlanInterface|null
     */
    public function getByCode(string $code): ?VindiPlanInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('code', $code);
        $collection->setPageSize(1);

        $item = $collection->getFirstItem();

        if (!$item->getId()) {
            return null;
        }

        $vindiplan = $this->vindiplanFactory->create();
        $this->resourceModel->load($vindiplan, $item->getId());

        return $vindiplan;
    }

    /**
     * Retrieve plan by name.
     *
     * @param string $name
     * @return VindiPlanInterface|null
     */
    public function getByName(string $name): ?VindiPlanInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('name', ['eq' => $name]);
        $collection->setPageSize(1);

        $item = $collection->getFirstItem();

        if (!$item->getId()) {
            return null;
        }

        $vindiplan = $this->vindiplanFactory->create();
        $this->resourceModel->load($vindiplan, $item->getId());

        return $vindiplan;
    }

    /**
     * Retrieve plan by Vindi ID.
     *
     * @param string $vindiId
     * @return VindiPlanInterface|null
     */
    public function getByVindiId(string $vindiId): ?VindiPlanInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('vindi_id', $vindiId);
        $collection->setPageSize(1);

        $item = $collection->getFirstItem();

        if (!$item->getId()) {
            return null;
        }

        $vindiplan = $this->vindiplanFactory->create();
        $this->resourceModel->load($vindiplan, $item->getId());

        return $vindiplan;
    }

    /**
     * Retrieve all Vindi IDs.
     *
     * @return string[]
     */
    public function getAllVindiIds(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect('vindi_id');

        $vindiIds = [];
        foreach ($collection as $item) {
            if (!empty($item->getData('vindi_id'))) {
                $vindiIds[] = $item->getData('vindi_id');
            }
        }

        return $vindiIds;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return VindiPlanSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): VindiPlanSearchResultInterface
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var VindiPlanSearchResultInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();

        $searchResult->setItems($collection->getItems())
            ->setSearchCriteria($searchCriteria)
            ->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @param VindiPlanInterface $vindiplan
     * @return void
     * @throws CouldNotSaveException
     */
    public function save(VindiPlanInterface $vindiplan): void
    {
        try {
            $this->resourceModel->save($vindiplan);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Error when saving vindiplan'));
        }
    }

    /**
     * @param VindiPlanInterface $vindiplan
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(VindiPlanInterface $vindiplan): void
    {
        try {
            $this->resourceModel->delete($vindiplan);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete vindiplan.'));
        }
    }
}
