<?php

namespace Vindi\Payment\Helper\WebHookHandlers;

class BillCanceled
{
    /**
     * @var \Vindi\Payment\Model\Payment\Bill
     */
    protected $bill;

    /**
     * @var \Vindi\Payment\Helper\WebHookHandlers\Order
     */
    protected $order;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Vindi\Payment\Model\Payment\Bill $bill,
        \Vindi\Payment\Helper\WebHookHandlers\Order $order,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->bill = $bill;
        $this->order = $order;
        $this->logger = $logger;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param array $data
     *
     * @return bool
     * @throws \Exception
     */
    public function billCanceled($data)
    {
        $bill = $data['bill'];

        if (!$bill) {
            $this->logger->error(__('Error while interpreting webhook "bill_canceled"'));
            return false;
        }

        $isSubscription = isset($bill['subscription']['id']);
        $order = null;

        if ($isSubscription) {
            $order = $this->getOrderByVindiBillAndSubscriptionId($bill['id'], $bill['subscription']['id']);
        }

        if (!$order) {
            $order = $this->getOrderFromBill($bill['id']);
        }

        if (!$order) {
            $this->logger->warning(__('Order not found'));
            return false;
        }

        $order->cancel();
        $order->addStatusHistoryComment(__(sprintf(
            'Vindi API: Order %s Canceled.',
            $order->getId()
        )));
        $this->orderRepository->save($order);

        $this->logger->info(__(sprintf(
            'Vindi API: Order %s Canceled.',
            $order->getId()
        )));

        return true;
    }

    private function getOrderFromBill($billId)
    {
        $bill = $this->bill->getBill($billId);

        if (!$bill) {
            return false;
        }

        return $this->order->getOrder(compact('bill'));
    }

    /**
     * @param $vindiBillId
     * @param $subscriptionId
     * @return bool|\Magento\Sales\Api\Data\OrderInterface
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
}
