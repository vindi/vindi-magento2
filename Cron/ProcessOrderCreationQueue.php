<?php

namespace Vindi\Payment\Cron;

use Psr\Log\LoggerInterface;
use Vindi\Payment\Api\OrderCreationQueueRepositoryInterface;
use Vindi\Payment\Helper\WebHookHandlers\OrderCreator;

class ProcessOrderCreationQueue
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderCreationQueueRepositoryInterface
     */
    private $orderCreationQueueRepository;

    /**
     * @var OrderCreator
     */
    private $orderCreator;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param OrderCreationQueueRepositoryInterface $orderCreationQueueRepository
     * @param OrderCreator $orderCreator
     */
    public function __construct(
        LoggerInterface $logger,
        OrderCreationQueueRepositoryInterface $orderCreationQueueRepository,
        OrderCreator $orderCreator
    ) {
        $this->logger = $logger;
        $this->orderCreationQueueRepository = $orderCreationQueueRepository;
        $this->orderCreator = $orderCreator;
    }

    /**
     * Process the oldest pending order creation request.
     */
    public function execute()
    {
        try {
            $queueItem = $this->orderCreationQueueRepository->getOldestPending();
            if (!$queueItem) {
                $this->logger->info(__('No pending order creation requests in the queue.'));
                return;
            }

            $billData = json_decode($queueItem->getBillData(), true);
            $result = $this->orderCreator->createOrderFromBill($billData);

            if ($result) {
                $queueItem->setStatus('completed');
            } else {
                $queueItem->setStatus('failed');
            }

            $this->orderCreationQueueRepository->save($queueItem);
        } catch (\Exception $e) {
            $this->logger->error(__('Error processing order creation queue: %1', $e->getMessage()));
        }
    }
}
