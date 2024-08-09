<?php

namespace Vindi\Payment\Helper\WebHookHandlers;

use Vindi\Payment\Api\OrderCreationQueueRepositoryInterface;
use Vindi\Payment\Model\OrderCreationQueueFactory;
use Magento\Sales\Model\OrderRepository;
use Vindi\Payment\Helper\EmailSender;
use Vindi\Payment\Logger\Logger;
use Magento\Sales\Model\Order\Invoice;
use Vindi\Payment\Helper\Data;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\InvoiceRepositoryInterface;

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
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Data
     */
    private $helperData;

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
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        InvoiceRepositoryInterface $invoiceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Data $helperData
    ) {
        $this->logger = $logger;
        $this->orderCreator = $orderCreator;
        $this->orderCreationQueueRepository = $orderCreationQueueRepository;
        $this->orderCreationQueueFactory = $orderCreationQueueFactory;
        $this->orderRepository = $orderRepository;
        $this->emailSender = $emailSender;
        $this->dbAdapter = $resourceConnection->getConnection();
        $this->invoiceRepository = $invoiceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->helperData = $helperData;
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

        $isSubscription = isset($bill['subscription']) && $bill['subscription'] != null;

        if ($isSubscription) {
            return $this->handleSubscriptionFlow($bill, $data);
        } else {
            return $this->handleRegularOrderFlow($bill);
        }
    }

    /**
     * Handle the subscription flow.
     *
     * @param array $bill
     * @param array $data
     * @return bool
     */
    private function handleSubscriptionFlow($bill, $data)
    {
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

    /**
     * Handle the regular order flow.
     *
     * @param array $bill
     * @return bool
     */
    private function handleRegularOrderFlow($bill)
    {
        $order = null;

        if (isset($bill['code']) && $bill['code'] != null) {
            $order = $this->getOrder($bill['code']);
        }

        if (!$order) {
            $this->logger->error(__('Order not found for bill code: %1', $bill['code']));
            return false;
        }

        return $this->createInvoice($order);
    }

    /**
     * Create an invoice for a regular order.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createInvoice(\Magento\Sales\Model\Order $order)
    {
        if (!$order->getId()) {
            return false;
        }

        $this->logger->info(__('Generating invoice for the order %1.', $order->getId()));

        if (!$order->canInvoice()) {
            $this->logger->error(__('Impossible to generate invoice for order %1.', $order->getId()));
            return false;
        }

        $invoice = $order->prepareInvoice();
        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->pay();
        $invoice->setSendEmail(true);
        $this->invoiceRepository->save($invoice);

        $this->logger->info(__('Invoice created successfully.'));

        $status = $this->helperData->getStatusToPaidOrder();
        if ($state = $this->helperData->getStatusState($status)) {
            $order->setState($state);
        }

        $order->addCommentToStatusHistory(
            __('The payment was confirmed and the order is being processed'),
            $status
        );

        $this->orderRepository->save($order);

        return true;
    }

    /**
     * Retrieve the order by increment ID.
     *
     * @param string $incrementId
     * @return \Magento\Sales\Api\Data\OrderInterface|false
     */
    private function getOrder($incrementId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId, 'eq')
            ->create();

        $orderList = $this->orderRepository
            ->getList($searchCriteria)
            ->getItems();

        try {
            return reset($orderList);
        } catch (\Exception $e) {
            $this->logger->error(__('Order #%1 not found', $incrementId));
            $this->logger->error($e->getMessage());
        }

        return false;
    }
}
