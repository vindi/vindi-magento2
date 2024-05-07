<?php

namespace Vindi\Payment\Helper\WebHookHandlers;

use Vindi\Payment\Api\OrderCreationQueueRepositoryInterface;
use Vindi\Payment\Model\OrderCreationQueueFactory;

/**
 * Class BillCreated
 */
class BillCreated
{
    /**
     * @var \Psr\Log\LoggerInterface
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
     * @param \Psr\Log\LoggerInterface $logger
     * @param OrderCreator $orderCreator
     * @param OrderCreationQueueRepositoryInterface $orderCreationQueueRepository
     * @param OrderCreationQueueFactory $orderCreationQueueFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        OrderCreator $orderCreator,
        OrderCreationQueueRepositoryInterface $orderCreationQueueRepository,
        OrderCreationQueueFactory $orderCreationQueueFactory
    ) {
        $this->logger = $logger;
        $this->orderCreator = $orderCreator;
        $this->orderCreationQueueRepository = $orderCreationQueueRepository;
        $this->orderCreationQueueFactory = $orderCreationQueueFactory;
    }

    /**
     * Handle 'bill_created' event.
     * The bill can be related to a subscription or a single payment.
     *
     * @param array $data
     *
     * @return bool
     */
    public function billCreated($data)
    {
        $bill = $data['bill'];

        if (!$bill) {
            $this->logger->error(__('Error while interpreting webhook "bill_created"'));
            return false;
        }

        if (!isset($bill['subscription']) || $bill['subscription'] === null || !isset($bill['subscription']['id'])) {
            $this->logger->info(__(sprintf('Ignoring the event "bill_created" for single sell')));
            return false;
        }

        $queueItem = $this->orderCreationQueueFactory->create();

        $queueItem->setData([
            'bill_data' => json_encode($data),
            'status'    => 'pending'
        ]);

        $this->orderCreationQueueRepository->save($queueItem);

        return true;
    }
}
