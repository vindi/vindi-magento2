<?php

namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\VindiSubscriptionItemDiscountInterface;
use Vindi\Payment\Api\Data\VindiSubscriptionItemDiscountSearchResultInterface;
use Vindi\Payment\Api\Data\VindiSubscriptionItemDiscountInterfaceFactory;
use Vindi\Payment\Api\Data\VindiSubscriptionItemDiscountSearchResultInterfaceFactory;
use Vindi\Payment\Api\VindiSubscriptionItemDiscountRepositoryInterface;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItemDiscount as ResourceModel;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItemDiscount\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class VindiSubscriptionItemDiscountRepository
 *
 * Repository for managing Vindi Subscription Item Discounts.
 */
class VindiSubscriptionItemDiscountRepository implements VindiSubscriptionItemDiscountRepositoryInterface
{
    /**
     * @var ResourceModel
     */
    private $resourceModel;

    /**
     * @var VindiSubscriptionItemDiscountInterfaceFactory
     */
    private $discountFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var VindiSubscriptionItemDiscountSearchResultInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * VindiSubscriptionItemDiscountRepository constructor.
     *
     * @param ResourceModel $resourceModel
     * @param VindiSubscriptionItemDiscountInterfaceFactory $discountFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param VindiSubscriptionItemDiscountSearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ResourceModel $resourceModel,
        VindiSubscriptionItemDiscountInterfaceFactory $discountFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        VindiSubscriptionItemDiscountSearchResultInterfaceFactory $searchResultsFactory
    ) {
        $this->resourceModel = $resourceModel;
        $this->discountFactory = $discountFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * Retrieve a discount by ID.
     *
     * @param int $entityId
     * @return VindiSubscriptionItemDiscountInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): VindiSubscriptionItemDiscountInterface
    {
        try {
            $discount = $this->discountFactory->create();
            $this->resourceModel->load($discount, $entityId);

            if (!$discount->getId()) {
                throw new NoSuchEntityException(__('Discount with ID %1 does not exist.', $entityId));
            }

            return $discount;
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Unable to find discount with ID %1.', $entityId));
        }
    }

    /**
     * Save a discount.
     *
     * @param VindiSubscriptionItemDiscountInterface $discount
     * @return void
     * @throws CouldNotSaveException
     */
    public function save(VindiSubscriptionItemDiscountInterface $discount): void
    {
        try {
            $this->resourceModel->save($discount);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save discount.'));
        }
    }

    /**
     * Delete a discount.
     *
     * @param VindiSubscriptionItemDiscountInterface $discount
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(VindiSubscriptionItemDiscountInterface $discount): void
    {
        try {
            $this->resourceModel->delete($discount);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete discount.'));
        }
    }

    /**
     * Retrieve a list of discounts based on search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return VindiSubscriptionItemDiscountSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): VindiSubscriptionItemDiscountSearchResultInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }
}
