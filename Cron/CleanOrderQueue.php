<?php
namespace Vindi\Payment\Cron;

use Vindi\Payment\Model\ResourceModel\OrderCreationQueue;
use Psr\Log\LoggerInterface;

class CleanOrderQueue
{
    /**
     * @var OrderCreationQueue
     */
    protected $orderCreationQueue;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param OrderCreationQueue $orderCreationQueue
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderCreationQueue $orderCreationQueue,
        LoggerInterface $logger
    ) {
        $this->orderCreationQueue = $orderCreationQueue;
        $this->logger = $logger;
    }

    /**
     * Execute the CRON job
     */
    public function execute()
    {
        try {
            $this->orderCreationQueue->deleteOldNonPendingRecords();
            $this->logger->info('CleanOrderQueue Cron Job executed successfully.');
        } catch (\Exception $e) {
            $this->logger->error('Error during CleanOrderQueue Cron Job: ' . $e->getMessage());
        }
    }
}
