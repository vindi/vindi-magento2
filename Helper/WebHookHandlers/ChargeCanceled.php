<?php

namespace Vindi\Payment\Helper\WebHookHandlers;

class ChargeCanceled
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

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Vindi\Payment\Model\Payment\Bill $bill,
        \Vindi\Payment\Helper\WebHookHandlers\Order $order,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->bill = $bill;
        $this->order = $order;
        $this->logger = $logger;
    }

    /**
     * @param array $data
     *
     * @return bool
     * @throws \Exception
     */
    public function chargeCanceled($data)
    {
        $charge = $data['charge'];

        /** @var \Magento\Sales\Model\Order $order */
        if (!($order = $this->getOrderFromBill($charge['bill']['id']))) {
            $this->logger->warning(__('Order not found'));

            return false;
        }

        $order->cancel();
        $order->addStatusHistoryComment(__(sprintf('Vindi API: Order Canceled.')));
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
}
