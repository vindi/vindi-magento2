<?php

namespace Vindi\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

/**
 * Class OrderSaveAfter
 * @package Vindi\Payment\Observer
 */
class OrderSaveAfter implements ObserverInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * OrderSaveAfter constructor.
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(ResourceConnection $resourceConnection, LoggerInterface $logger)
    {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        $incrementId = $order->getIncrementId();
        $orderId     = $order->getId();
        $orderStatus = $order->getStatus();

        if ($orderId && $incrementId) {
            $connection = $this->resourceConnection->getConnection();
            $tableName  = $this->resourceConnection->getTableName('vindi_subscription_orders');

            try {
                $connection->update(
                    $tableName,
                    ['order_id' => $orderId, 'status' => $orderStatus],
                    ['increment_id = ?' => $incrementId]
                );
            } catch (\Exception $e) {
                $this->logger->error('Error updating the vindi_subscription_orders table.', ['exception' => $e]);
            }
        } else {
            $this->logger->info('Order ID or Increment ID is missing.', ['increment_id' => $incrementId, 'order_id' => $orderId]);
        }
    }
}
