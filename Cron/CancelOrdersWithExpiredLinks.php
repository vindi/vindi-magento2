<?php

declare(strict_types=1);

namespace Vindi\Payment\Cron;

use Vindi\Payment\Model\PaymentLinkFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class CancelOrdersWithExpiredLinks
{
    /**
     * @var PaymentLinkFactory
     */
    private PaymentLinkFactory $paymentLinkFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var OrderManagementInterface
     */
    private OrderManagementInterface $orderManagement;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param PaymentLinkFactory $paymentLinkFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManagementInterface $orderManagement
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     */
    public function __construct(
        PaymentLinkFactory $paymentLinkFactory,
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement,
        DateTime $dateTime,
        LoggerInterface $logger
    ) {
        $this->paymentLinkFactory = $paymentLinkFactory;
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
    }

    /**
     * Execute the cron job
     */
    public function execute(): void
    {
        try {
            $currentDate = $this->dateTime->gmtDate();
            $paymentLinkCollection = $this->paymentLinkFactory->create()->getCollection()
                ->addFieldToFilter('expired_at', ['notnull' => true])
                ->addFieldToFilter('expired_at', ['lt' => date('Y-m-d H:i:s', strtotime('-30 days', strtotime($currentDate)))]);

            foreach ($paymentLinkCollection as $paymentLink) {
                $orderId = $paymentLink->getOrderId();
                $order = $this->orderRepository->get($orderId);

                if ($order && $order->canCancel()) {
                    $this->orderManagement->cancel($orderId);
                    $this->logger->info(sprintf('Order ID %s has been canceled due to expired payment link.', $orderId));
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error in canceling orders with expired links: ' . $e->getMessage());
        }
    }
}

