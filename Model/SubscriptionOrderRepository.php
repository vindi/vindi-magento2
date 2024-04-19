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
 * @author Iago Cedran <iago@bizcommerce.com.br>
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
    private $subscriptionorderFactory;

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
     * @param SubscriptionOrderInterfaceFactory $subscriptionorderFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SubscriptionOrderSearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ResourceModel $resourceModel,
        SubscriptionOrderInterfaceFactory $subscriptionorderFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        SubscriptionOrderSearchResultInterfaceFactory $searchResultsFactory
    ) {
        $this->resourceModel        = $resourceModel;
        $this->subscriptionorderFactory          = $subscriptionorderFactory;
        $this->collectionFactory    = $collectionFactory;
        $this->collectionProcessor  = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param int $entityId
     * @return SubscriptionOrderInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): SubscriptionOrderInterface
    {
        try {
            /** @var SubscriptionOrderInterface $subscriptionorder */
            $subscriptionorder = $this->subscriptionorderFactory->create();
            $this->resourceModel->load($subscriptionorder, $entityId);
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Error during load subscriptionorder by Entity ID'));
        }
        return $subscriptionorder;
    }

    /**
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
     * @param SubscriptionOrderInterface $subscriptionorder
     * @return void
     * @throws CouldNotSaveException
     */
    public function save(SubscriptionOrderInterface $subscriptionorder): void
    {
        try {
            $this->resourceModel->save($subscriptionorder);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Error when saving subscriptionorder'));
        }
    }

    /**
     * @param SubscriptionOrderInterface $subscriptionorder
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(SubscriptionOrderInterface $subscriptionorder): void
    {
        try {
            $this->resourceModel->delete($subscriptionorder);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete subscriptionorder.'));
        }
    }
}
