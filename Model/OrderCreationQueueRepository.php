<?php

namespace Vindi\Payment\Model;

use Vindi\Payment\Api\OrderCreationQueueRepositoryInterface;
use Vindi\Payment\Model\ResourceModel\OrderCreationQueue as ResourceModel;
use Vindi\Payment\Model\ResourceModel\OrderCreationQueue\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class OrderCreationQueueRepository implements OrderCreationQueueRepositoryInterface
{
    /**
     * @var ResourceModel
     */
    private $resource;

    /**
     * @var OrderCreationQueueFactory
     */
    private $orderCreationQueueFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Constructor
     *
     * @param ResourceModel $resource
     * @param OrderCreationQueueFactory $orderCreationQueueFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ResourceModel $resource,
        OrderCreationQueueFactory $orderCreationQueueFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->orderCreationQueueFactory = $orderCreationQueueFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Save a queue item
     *
     * @param OrderCreationQueue $queueItem
     * @return OrderCreationQueue
     */
    public function save(OrderCreationQueue $queueItem)
    {
        $this->resource->save($queueItem);
        return $queueItem;
    }

    /**
     * Get a queue item by ID
     *
     * @param int $id
     * @return OrderCreationQueue
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $queueItem = $this->orderCreationQueueFactory->create();
        $this->resource->load($queueItem, $id);
        if (!$queueItem->getId()) {
            throw new NoSuchEntityException(__('Queue item with ID "%1" does not exist.', $id));
        }
        return $queueItem;
    }

    /**
     * Get the oldest pending queue item by type
     *
     * @param string $type
     * @return OrderCreationQueue|null
     */
    public function getOldestPending($type = 'bill_created')
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('status', 'pending')
            ->addFieldToFilter('type', $type)
            ->setOrder('created_at', 'ASC')
            ->setPageSize(1)
            ->getFirstItem();

        return $collection->getId() ? $collection : null;
    }

    /**
     * Delete a queue item
     *
     * @param OrderCreationQueue $queueItem
     * @return void
     */
    public function delete(OrderCreationQueue $queueItem)
    {
        $this->resource->delete($queueItem);
    }
}
