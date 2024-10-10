<?php

declare(strict_types=1);

namespace Vindi\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Vindi\Payment\Model\PaymentLinkService;
use Psr\Log\LoggerInterface;

class CancelOrderObserver implements ObserverInterface
{
    /**
     * @var PaymentLinkService
     */
    private PaymentLinkService $paymentLinkService;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param PaymentLinkService $paymentLinkService
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        PaymentLinkService $paymentLinkService,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        $this->paymentLinkService = $paymentLinkService;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    /**
     * Execute observer to handle order cancellation
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        try {
            $order = $observer->getEvent()->getOrder();

            if ($order->isCanceled()) {
                $orderId = $order->getEntityId();

                $paymentLink = $this->paymentLinkService->getPaymentLink($orderId);

                if ($paymentLink && $paymentLink->getId()) {
                    if ($paymentLink->getStatus() !== 'processed') {
                        $paymentLink->setStatus('processed');
                        $this->paymentLinkService->savePaymentLink($paymentLink);

                        $this->logger->info(sprintf('Payment link for order ID %s has been updated to "processed".', $orderId));
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error while updating payment link for canceled order: ' . $e->getMessage());
        }
    }
}

