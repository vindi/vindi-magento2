<?php

namespace Vindi\Payment\Api;

use Vindi\Payment\Model\OrderCreationQueue;

interface OrderCreationQueueRepositoryInterface
{
    /**
     * Save a queue item
     *
     * @param OrderCreationQueue $queueItem
     * @return OrderCreationQueue
     */
    public function save(OrderCreationQueue $queueItem);

    /**
     * Get a queue item by ID
     *
     * @param int $id
     * @return OrderCreationQueue
     */
    public function getById($id);

    /**
     * Get the oldest pending queue item
     *
     * @return OrderCreationQueue|null
     */
    public function getOldestPending();

    /**
     * Delete a queue item
     *
     * @param OrderCreationQueue $queueItem
     * @return void
     */
    public function delete(OrderCreationQueue $queueItem);
}
