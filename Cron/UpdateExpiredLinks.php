<?php

declare(strict_types=1);

namespace Vindi\Payment\Cron;

use Vindi\Payment\Model\PaymentLinkService;
use Psr\Log\LoggerInterface;

class UpdateExpiredLinks
{
    /**
     * @var PaymentLinkService
     */
    private PaymentLinkService $paymentLinkService;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param PaymentLinkService $paymentLinkService
     * @param LoggerInterface $logger
     */
    public function __construct(
        PaymentLinkService $paymentLinkService,
        LoggerInterface $logger
    ) {
        $this->paymentLinkService = $paymentLinkService;
        $this->logger = $logger;
    }

    /**
     * Execute the cron job to update the status of expired payment links.
     */
    public function execute(): void
    {
        try {
            $this->paymentLinkService->updateExpiredPaymentLinks();
            $this->logger->info('Expired payment links updated successfully.');
        } catch (\Exception $e) {
            $this->logger->error('Error updating expired payment links: ' . $e->getMessage());
        }
    }
}
