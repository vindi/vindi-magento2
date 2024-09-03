<?php
namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\VindiCustomerInterface;
use Vindi\Payment\Api\Data\VindiCustomerSearchResultsInterface;
use Vindi\Payment\Api\Data\VindiCustomerSearchResultsInterfaceFactory;
use Vindi\Payment\Api\VindiCustomerRepositoryInterface;
use Vindi\Payment\Model\ResourceModel\VindiCustomer as VindiCustomerResource;
use Vindi\Payment\Model\ResourceModel\VindiCustomer\CollectionFactory as VindiCustomerCollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * Class VindiCustomerRepository
 *
 * Implementation of the VindiCustomerRepositoryInterface.
 */
class VindiCustomerRepository implements VindiCustomerRepositoryInterface
{
    /**
     * @var VindiCustomerResource
     */
    protected $resource;

    /**
     * @var VindiCustomerFactory
     */
    protected $vindiCustomerFactory;

    /**
     * @var VindiCustomerCollectionFactory
     */
    protected $vindiCustomerCollectionFactory;

    /**
     * @var VindiCustomerSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * VindiCustomerRepository constructor.
     *
     * @param VindiCustomerResource $resource
     * @param VindiCustomerFactory $vindiCustomerFactory
     * @param VindiCustomerCollectionFactory $vindiCustomerCollectionFactory
     * @param VindiCustomerSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        VindiCustomerResource $resource,
        VindiCustomerFactory $vindiCustomerFactory,
        VindiCustomerCollectionFactory $vindiCustomerCollectionFactory,
        VindiCustomerSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->vindiCustomerFactory = $vindiCustomerFactory;
        $this->vindiCustomerCollectionFactory = $vindiCustomerCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritdoc
     */
    public function save(VindiCustomerInterface $vindiCustomer)
    {
        try {
            $this->resource->save($vindiCustomer);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Could not save the Vindi Customer: %1', $exception->getMessage()));
        }
        return $vindiCustomer;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        $vindiCustomer = $this->vindiCustomerFactory->create();
        $this->resource->load($vindiCustomer, $id);
        if (!$vindiCustomer->getId()) {
            throw new NoSuchEntityException(__('The Vindi Customer with the "%1" ID doesn\'t exist.', $id));
        }
        return $vindiCustomer;
    }

    /**
     * @inheritdoc
     */
    public function delete(VindiCustomerInterface $vindiCustomer)
    {
        try {
            $this->resource->delete($vindiCustomer);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Could not delete the Vindi Customer: %1', $exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->vindiCustomerCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
