<?php

namespace Vindi\Payment\Helper\WebHookHandlers;

use Vindi\Payment\Api\OrderCreationQueueRepositoryInterface;
use Vindi\Payment\Model\OrderCreationQueueFactory;
use Magento\Sales\Model\OrderRepository;
use Vindi\Payment\Helper\EmailSender;
use Vindi\Payment\Logger\Logger;

/**
 * Class BillPaid
 */
class BillPaid
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var OrderCreator
     */
    private $orderCreator;

    /**
     * @var OrderCreationQueueRepositoryInterface
     */
    private $orderCreationQueueRepository;

    /**
     * @var OrderCreationQueueFactory
     */
    private $orderCreationQueueFactory;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $dbAdapter;

    /**
     * Constructor for initializing class dependencies.
     */
    public function __construct(
        Logger $logger,
        OrderCreator $orderCreator,
        OrderCreationQueueRepositoryInterface $orderCreationQueueRepository,
        OrderCreationQueueFactory $orderCreationQueueFactory,
        OrderRepository $orderRepository,
        EmailSender $emailSender,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->logger = $logger;
        $this->orderCreator = $orderCreator;
        $this->orderCreationQueueRepository = $orderCreationQueueRepository;
        $this->orderCreationQueueFactory = $orderCreationQueueFactory;
        $this->orderRepository = $orderRepository;
        $this->emailSender = $emailSender;
        $this->dbAdapter = $resourceConnection->getConnection();
    }

    /**
     * Handle the "bill_paid" webhook.
     *
     * @param array $data
     * @return bool
     */
    public function billPaid($data)
    {
        $bill = $data['bill'];

        if (!$bill) {
            $this->logger->error(__('Error while interpreting webhook "bill_paid"'));
            return false;
        }

        $subscriptionId = $bill['subscription']['id'];
        $lockName = 'vindi_subscription_' . $subscriptionId;
        if (!$this->dbAdapter->query("SELECT GET_LOCK(?, 10)", [$lockName])->fetchColumn()) {
            $this->logger->error(__('Could not acquire lock for subscription ID: %1', $subscriptionId));
            return false;
        }

        try {
            $originalOrder = $this->orderCreator->getOrderFromSubscriptionId($subscriptionId);
            if ($originalOrder) {
                $queueItem = $this->orderCreationQueueFactory->create();
                $queueItem->setData([
                    'bill_data' => json_encode($data),
                    'status'    => 'pending',
                    'type'      => 'bill_paid'
                ]);
                $this->orderCreationQueueRepository->save($queueItem);
                $this->logger->info(__('Created order creation queue item for subscription.'));
            } else {
                $this->logger->info(__('No corresponding order found for subscription ID: %1. Ignoring event.', $subscriptionId));
            }

            return true;
        } finally {
            $this->dbAdapter->query("SELECT RELEASE_LOCK(?)", [$lockName]);
        }
    }
}

