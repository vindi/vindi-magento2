<?php

namespace Vindi\Payment\Cron;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\ItemFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Psr\Log\LoggerInterface;
use Vindi\Payment\Api\OrderCreationQueueRepositoryInterface;

/**
 * ProcessOrderPaidQueue
 */
class ProcessOrderPaidQueue
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
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ItemFactory
     */
    private $invoiceItemFactory;

    /**
     * Lock name for this cron job
     */
    private const LOCK_NAME = 'vindi_payment_process_order_paid_queue';


    /**
     * ProcessOrderPaidQueue constructor.
     * @param LoggerInterface $logger
     * @param OrderCreationQueueRepositoryInterface $orderCreationQueueRepository
     * @param InvoiceService $invoiceService
     * @param OrderRepositoryInterface $orderRepository
     * @param Transaction $transaction
     * @param LockManagerInterface $lockManager
     * @param InvoiceSender $invoiceSender
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ItemFactory $invoiceItemFactory
     */
    public function __construct(
        LoggerInterface $logger,
        OrderCreationQueueRepositoryInterface $orderCreationQueueRepository,
        InvoiceService $invoiceService,
        OrderRepositoryInterface $orderRepository,
        Transaction $transaction,
        LockManagerInterface $lockManager,
        InvoiceSender $invoiceSender,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ItemFactory $invoiceItemFactory
    ) {
        $this->logger = $logger;
        $this->orderCreationQueueRepository = $orderCreationQueueRepository;
        $this->invoiceService = $invoiceService;
        $this->orderRepository = $orderRepository;
        $this->transaction = $transaction;
        $this->lockManager = $lockManager;
        $this->invoiceSender = $invoiceSender;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->invoiceItemFactory = $invoiceItemFactory;
    }

    /**
     * @return void
     */
    public function execute()
    {
        if (!$this->lockManager->lock(self::LOCK_NAME)) {
            $this->logger->info(__('The job is already running.'));
            return;
        }

        try {
            $queueItem = $this->orderCreationQueueRepository->getOldestPending('bill_paid');
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

            $result = $this->createInvoiceFromBill($billData);

            if ($result === true) {
                $queueItem->setStatus('completed');
                $this->logger->info(__('Successfully processed order creation queue item ID %1', $queueItem->getId()));
            } elseif ($result === false) {
                $queueItem->setStatus('failed');
                $this->logger->error(__('Failed to process order creation queue item ID %1', $queueItem->getId()));
            }

            if ($result !== null) {
                $this->orderCreationQueueRepository->save($queueItem);
            }
        } catch (LocalizedException $e) {
            $this->logger->error(__('Error processing order creation queue: %1', $e->getMessage()));
        } catch (\Exception $e) {
            $this->logger->error(__('Unexpected error processing order creation queue: %1', $e->getMessage()));
        } finally {
            $this->lockManager->unlock(self::LOCK_NAME);
        }
    }

    /**
     * Create invoice from bill data.
     *
     * @param array $billData
     * @return bool
     */
    protected function createInvoiceFromBill($billData)
    {
        try {
            if (empty($billData['bill']) || empty($billData['bill']['subscription'])) {
                throw new LocalizedException(__('Invalid bill data structure.'));
            }

            $bill = $billData['bill'];
            $subscriptionId = $bill['subscription']['id'];
            $order = $this->getOrderFromBillIdAndSubscriptionId($bill['id'], $subscriptionId);

            if (!$order) {
                $this->logger->info(__('Order not found for subscription ID %1', $subscriptionId));
                return false;
            }

            $invoice = $this->invoiceService->prepareInvoice($order);
            if (!$invoice) {
                throw new LocalizedException(__('We can\'t create an invoice without products.'));
            }

            $baseGrandeTotal    = $order->getBaseGrandTotal();
            $grandTotal         = $order->getGrandTotal();
            $baseShippingAmount = $order->getBaseShippingAmount();
            $shippingAmount     = $order->getShippingAmount();
            $baseSubtotal       = $order->getBaseSubtotal();
            $subtotal           = $order->getSubtotal();

            $orderItems = $order->getAllItems();
            $invoiceItems = [];
            foreach ($orderItems as $item) {
                $invoiceItem = $this->invoiceItemFactory->create();
                $invoiceItem->setOrderItem($item);
                $invoiceItem->setQty($item->getQtyOrdered());
                $invoiceItem->setPrice($item->getPrice());
                $invoiceItem->setBasePrice($item->getBasePrice());
                $invoiceItem->setRowTotal($item->getRowTotal());
                $invoiceItem->setBaseRowTotal($item->getBaseRowTotal());
                $invoiceItems[$item->getItemId()] = $invoiceItem;
            }

            foreach ($invoiceItems as $invoiceItem) {
                $invoice->addItem($invoiceItem);
            }

            $invoice->setBaseSubtotal($baseSubtotal);
            $invoice->setSubtotal($subtotal);
            $invoice->setBaseGrandTotal($baseGrandeTotal);
            $invoice->setGrandTotal($grandTotal);
            $invoice->setBaseShippingAmount($baseShippingAmount);
            $invoice->setShippingAmount($shippingAmount);

            $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
            $invoice->register();
            $invoice->pay();
            $invoice->setSendEmail(true);

            $this->transaction->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();

            $this->invoiceSender->send($invoice);

            $order->addCommentToStatusHistory(__('Invoice created for order ID %1', $order->getId()))
                ->setIsCustomerNotified(true);

            $order->setState('processing')
                ->setStatus('processing');

            $this->orderRepository->save($order);

            $this->logger->info(__('Invoice created for order ID %1', $order->getId()));
            return true;
        } catch (NoSuchEntityException $e) {
            $this->logger->error(__('Order not found for subscription ID %1', $subscriptionId));
            return false;
        } catch (LocalizedException $e) {
            $this->logger->error(__('Error creating invoice: %1', $e->getMessage()));
            return false;
        } catch (\Exception $e) {
            $this->logger->error(__('Unexpected error creating invoice: %1', $e->getMessage()));
            return false;
        }
    }

    /**
     * Get order from subscription ID.
     *
     * @param string $subscriptionId
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    protected function getOrderFromSubscriptionId($subscriptionId)
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('vindi_subscription_id', $subscriptionId)
                ->create();

            $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
            return reset($orderList);
        } catch (\Exception $e) {
            $this->logger->error(__('Error fetching order for subscription ID %1: %2', $subscriptionId, $e->getMessage()));
            return null;
        }
    }

    /**
     * Get order from bill ID.
     *
     * @param string $billId
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    protected function getOrderFromBillId($billId)
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('vindi_bill_id', $billId)
                ->create();

            $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
            return reset($orderList);
        } catch (\Exception $e) {
            $this->logger->error(__('Error fetching order for bill ID %1: %2', $billId, $e->getMessage()));
            return null;
        }
    }

    /**
     * Get order from bill ID and subscription ID.
     *
     * @param string $billId
     * @param string $subscriptionId
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    protected function getOrderFromBillIdAndSubscriptionId($billId, $subscriptionId)
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('vindi_bill_id', $billId)
                ->addFilter('vindi_subscription_id', $subscriptionId)
                ->create();

            $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
            return reset($orderList);
        } catch (\Exception $e) {
            $this->logger->error(__('Error fetching order for bill ID %1: %2', $billId, $e->getMessage()));
            return null;
        }
    }
}
