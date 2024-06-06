<?php

namespace Vindi\Payment\Helper\WebHookHandlers;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Invoice;
use Vindi\Payment\Helper\Data;

/**
 * Class BillPaid
 */
class BillPaid
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * BillPaid constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Order $order
     * @param Data $helperData
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        Order $order,
        Data $helperData
    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->order = $order;
        $this->helperData = $helperData;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param $data
     * @return bool
     */
    public function billPaid($data)
    {
        $order = null;
        $isSubscription = false;

        if (isset($data['bill']['id']) && isset($data['bill']['subscription']['id'])) {
            $order = $this->getOrderByVindiBillAndSubscriptionId($data['bill']['id'], $data['bill']['subscription']['id']);
            $isSubscription = true;
        } elseif (isset($data['bill']['id']) && $data['bill']['id'] != null) {
            $order = $this->getOrderByVindiBillId($data['bill']['id']);
        }

        if (!$order && !($order = $this->order->getOrder($data))) {
            $this->logger->error(
                __(sprintf(
                    'There is no cycle %s of signature %d.',
                    $data['bill']['period']['cycle'],
                    $data['bill']['subscription']['id']
                ))
            );

            return false;
        }

        return $this->createInvoice($order, $isSubscription);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param bool $isSubscription
     * @return bool
     */
    public function createInvoice(\Magento\Sales\Model\Order $order, $isSubscription = false)
    {
        if (!$order->getId()) {
            return false;
        }

        $this->logger->info(__(sprintf('Generating invoice for the order %s.', $order->getId())));

        if (!$isSubscription) {
            if (!$order->canInvoice()) {
                $this->logger->error(__(sprintf('Impossible to generate invoice for order %s.', $order->getId())));
                return false;
            }
        }

        $invoice = $order->prepareInvoice();
        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->pay();
        $invoice->setSendEmail(true);
        $this->invoiceRepository->save($invoice);
        $this->logger->info(__('Invoice created with success'));

        if ($isSubscription) {
            $order->addCommentToStatusHistory(
                __('The payment was confirmed and the subscription is being processed')->getText(),
                \Magento\Sales\Model\Order::STATE_PROCESSING
            );
        } else {
            $status = $this->helperData->getStatusToPaidOrder();

            if ($state = $this->helperData->getStatusState($status)) {
                $order->setState($state);
            }

            $order->addCommentToStatusHistory(
                __('The payment was confirmed and the order is being processed')->getText(),
                $status
            );
        }

        $this->orderRepository->save($order);

        return true;
    }

    /**
     * @param $vindiBillId
     * @param $subscriptionId
     * @return bool|OrderInterface
     */
    private function getOrderByVindiBillAndSubscriptionId($vindiBillId, $subscriptionId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('vindi_bill_id', $vindiBillId, 'eq')
            ->addFilter('vindi_subscription_id', $subscriptionId, 'eq')
            ->create();

        $orderList = $this->orderRepository
            ->getList($searchCriteria)
            ->getItems();

        try {
            return reset($orderList);
        } catch (\Exception $e) {
            $this->logger->error(__('Order with Vindi Bill ID #%1 and Subscription ID #%2 not found', $vindiBillId, $subscriptionId));
            $this->logger->error($e->getMessage());
        }

        return false;
    }

    /**
     * @param $vindiBillId
     * @return bool|OrderInterface
     */
    private function getOrderByVindiBillId($vindiBillId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('vindi_bill_id', $vindiBillId, 'eq')
            ->create();

        $orderList = $this->orderRepository
            ->getList($searchCriteria)
            ->getItems();

        try {
            return reset($orderList);
        } catch (\Exception $e) {
            $this->logger->error(__('Order with Vindi Bill ID #%1 not found', $vindiBillId));
            $this->logger->error($e->getMessage());
        }

        return false;
    }

    /**
     * @param $subscriptionId
     * @return bool|OrderInterface
     */
    private function getOrderBySubscriptionId($subscriptionId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('vindi_subscription_id', $subscriptionId, 'eq')
            ->create();

        $orderList = $this->orderRepository
            ->getList($searchCriteria)
            ->getItems();

        try {
            return reset($orderList);
        } catch (\Exception $e) {
            $this->logger->error(__('Order with Subscription ID #%1 not found', $subscriptionId));
            $this->logger->error($e->getMessage());
        }

        return false;
    }
}
