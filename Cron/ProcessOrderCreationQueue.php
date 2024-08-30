<?php

namespace Vindi\Payment\Cron;

use Psr\Log\LoggerInterface;
use Vindi\Payment\Api\OrderCreationQueueRepositoryInterface;
use Vindi\Payment\Helper\WebHookHandlers\OrderCreator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Lock\LockManagerInterface;

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
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * Lock name for this cron job
     */
    private const LOCK_NAME = 'vindi_payment_process_order_creation_queue';

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param OrderCreationQueueRepositoryInterface $orderCreationQueueRepository
     * @param OrderCreator $orderCreator
     * @param LockManagerInterface $lockManager
     */
    public function __construct(
        LoggerInterface $logger,
        OrderCreationQueueRepositoryInterface $orderCreationQueueRepository,
        OrderCreator $orderCreator,
        LockManagerInterface $lockManager
    ) {
        $this->logger = $logger;
        $this->orderCreationQueueRepository = $orderCreationQueueRepository;
        $this->orderCreator = $orderCreator;
        $this->lockManager = $lockManager;
    }

    /**
     * Process the oldest pending order creation request.
     */
    public function execute()
    {
        if (!$this->lockManager->lock(self::LOCK_NAME)) {
            $this->logger->info(__('The job is already running.'));
            return;
        }

        try {
            $queueItem = $this->orderCreationQueueRepository->getOldestPending();
            if (!$queueItem) {
                $this->logger->info(__('No pending order creation requests in the queue.'));
                return;
            }

            $billData = json_decode($queueItem->getBillData(), true);

            if (!$billData) {
                $this->logger->error(__('Invalid bill data in the queue item ID %1', $queueItem->getId()));
                $queueItem->setStatus('failed');
                $this->orderCreationQueueRepository->save($queueItem);
                return;
            }

            $result = $this->orderCreator->createOrderFromBill($billData);

            if ($result) {
                $queueItem->setStatus('completed');
                $this->logger->info(__('Successfully processed order creation queue item ID %1', $queueItem->getId()));
            } else {
                $queueItem->setStatus('failed');
                $this->logger->error(__('Failed to process order creation queue item ID %1', $queueItem->getId()));
            }

            $this->orderCreationQueueRepository->save($queueItem);
        } catch (LocalizedException $e) {
            $this->logger->error(__('Error processing order creation queue: %1', $e->getMessage()));
        } catch (\Exception $e) {
            $this->logger->error(__('Unexpected error processing order creation queue: %1', $e->getMessage()));
        } finally {
            $this->lockManager->unlock(self::LOCK_NAME);
        }
    }
}
