<?php

declare(strict_types=1);

namespace Vindi\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Vindi\Payment\Model\PaymentLinkService;
use Psr\Log\LoggerInterface;

class InvoicePaymentLinkObserver implements ObserverInterface
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
     * Execute observer to update payment link status after invoice creation
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        try {
            $invoice = $observer->getEvent()->getInvoice();
            $order = $invoice->getOrder();

            if ($order->hasInvoices()) {
                $this->logger->info(sprintf('Order ID %s has already been invoiced.', $order->getEntityId()));

                $orderId = $order->getEntityId();
                $paymentLink = $this->paymentLinkService->getPaymentLink($orderId);

                if ($paymentLink && $paymentLink->getId()) {
                    if ($paymentLink->getStatus() !== 'processed') {
                        $paymentLink->setStatus('processed');
                        $this->paymentLinkService->savePaymentLink($paymentLink);

                        $this->logger->info(sprintf('Payment link for order ID %s has been updated to "processed" after invoice generation.', $orderId));
                    }
                }
            } else {
                $this->logger->info(sprintf('Order ID %s has not been invoiced yet.', $order->getEntityId()));
            }
        } catch (\Exception $e) {
            $this->logger->error('Error while updating payment link after invoice generation: ' . $e->getMessage());
        }
    }
}
