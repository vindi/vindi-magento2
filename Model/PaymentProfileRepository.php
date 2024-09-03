<?php
declare(strict_types=1);

namespace Vindi\Payment\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vindi\Payment\Api\Data\PaymentProfileInterface;
use Vindi\Payment\Api\Data\PaymentProfileSearchResultInterface;
use Vindi\Payment\Api\Data\PaymentProfileSearchResultInterfaceFactory;
use Vindi\Payment\Api\PaymentProfileRepositoryInterface;
use Vindi\Payment\Model\ResourceModel\PaymentProfile as ResourcePaymentProfile;
use Vindi\Payment\Model\ResourceModel\PaymentProfile\CollectionFactory as PaymentProfileCollectionFactory;
use Vindi\Payment\Model\PaymentProfileFactory as ModelPaymentProfileFactory;

class PaymentProfileRepository implements PaymentProfileRepositoryInterface
{
    protected $resource;
    protected $paymentProfileFactory;
    protected $paymentProfileCollectionFactory;
    protected $searchResultsFactory;
    protected $collectionProcessor;

    public function __construct(
        ResourcePaymentProfile $resource,
        ModelPaymentProfileFactory $paymentProfileFactory,
        PaymentProfileCollectionFactory $paymentProfileCollectionFactory,
        PaymentProfileSearchResultInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->paymentProfileFactory = $paymentProfileFactory;
        $this->paymentProfileCollectionFactory = $paymentProfileCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    public function save(PaymentProfileInterface $paymentProfile)
    {
        try {
            $this->resource->save($paymentProfile);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Could not save the payment profile: %1', $exception->getMessage()));
        }

        return $paymentProfile;
    }

    public function getByProfileId($profileId)
    {
        $paymentProfile = $this->paymentProfileFactory->create();
        $this->resource->load($paymentProfile, $profileId, 'payment_profile_id');
        if (!$paymentProfile->getId()) {
            throw new NoSuchEntityException(__('Payment profile with id "%1" does not exist.', $profileId));
        }
        return $paymentProfile;
    }

    public function getById($entityId)
    {
        $paymentProfile = $this->paymentProfileFactory->create();
        $this->resource->load($paymentProfile, $entityId);
        if (!$paymentProfile->getId()) {
            throw new NoSuchEntityException(__('Payment profile with id "%1" does not exist.', $entityId));
        }
        return $paymentProfile;
    }

    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->paymentProfileCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    public function delete(PaymentProfileInterface $paymentProfile)
    {
        try {
            $this->resource->delete($paymentProfile);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Could not delete the payment profile: %1', $exception->getMessage()));
        }
        return true;
    }

    public function deleteById($entityId)
    {
        return $this->delete($this->getById($entityId));
    }
}
