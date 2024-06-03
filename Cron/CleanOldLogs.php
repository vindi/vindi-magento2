<?php
namespace Vindi\Payment\Cron;

use Vindi\Payment\Model\ResourceModel\Log\CollectionFactory as LogCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class CleanOldLogs
{
    /**
     * @var LogCollectionFactory
     */
    protected $logCollectionFactory;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CleanOldLogs constructor.
     * @param LogCollectionFactory $logCollectionFactory
     * @param ResourceConnection $resource
     * @param LoggerInterface $logger
     */
    public function __construct(
        LogCollectionFactory $logCollectionFactory,
        ResourceConnection $resource,
        LoggerInterface $logger
    ) {
        $this->logCollectionFactory = $logCollectionFactory;
        $this->resource = $resource;
        $this->logger = $logger;
    }

    /**
     * Execute the CRON job
     */
    public function execute()
    {
        $connection = $this->resource->getConnection();
        $logTable = $connection->getTableName('vindi_api_logs');

        $date = new \DateTime();
        $date->modify('-45 days');
        $formattedDate = $date->format('Y-m-d H:i:s');

        try {
            $connection->beginTransaction();
            $connection->delete($logTable, ['created_at < ?' => $formattedDate]);
            $connection->commit();
            $this->logger->info('CleanOldLogs CRON job executed successfully.');
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->error('Error executing CleanOldLogs CRON job: ' . $e->getMessage());
        }
    }
}
