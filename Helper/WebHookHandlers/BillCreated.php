<?php

namespace Vindi\Payment\Helper\WebHookHandlers;

use Vindi\Payment\Api\OrderCreationQueueRepositoryInterface;
use Vindi\Payment\Model\OrderCreationQueueFactory;
use Magento\Sales\Model\OrderRepository;
use Vindi\Payment\Helper\EmailSender;

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
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * Constructor for initializing class dependencies.
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        OrderCreator $orderCreator,
        OrderCreationQueueRepositoryInterface $orderCreationQueueRepository,
        OrderCreationQueueFactory $orderCreationQueueFactory,
        OrderRepository $orderRepository,
        EmailSender $emailSender
    ) {
        $this->logger = $logger;
        $this->orderCreator = $orderCreator;
        $this->orderCreationQueueRepository = $orderCreationQueueRepository;
        $this->orderCreationQueueFactory = $orderCreationQueueFactory;
        $this->orderRepository = $orderRepository;
        $this->emailSender = $emailSender;
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
            $this->logger->info(__('Ignoring the event "bill_created" for single sell'));
            return false;
        }

        $originalOrder = $this->orderCreator->getOrderFromSubscriptionId($bill['subscription']['id']);
        if ($originalOrder && ($originalOrder->getPayment()->getMethod() === 'vindi_pix' || $originalOrder->getPayment()->getMethod() === 'vindi_bankslippix')) {
            $vindiBillId = (int) $originalOrder->getData('vindi_bill_id');
            if ($vindiBillId === null || $vindiBillId === '' || $vindiBillId === 0) {
                $originalOrder->setData('vindi_bill_id', $bill['id']);
                $this->orderRepository->save($originalOrder);
                $this->logger->info(__('Vindi bill ID set for the order.'));

                $this->emailSender->sendQrCodeAvailableEmail($originalOrder);
                return true;
            }
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
