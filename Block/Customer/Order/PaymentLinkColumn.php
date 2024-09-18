<?php

declare(strict_types=1);

namespace Vindi\Payment\Block\Customer\Order;

use Magento\Framework\View\Element\Template;
use Vindi\Payment\Model\PaymentLinkService;
use Magento\Sales\Api\OrderRepositoryInterface;

class PaymentLinkColumn extends Template
{
    /**
     * @var PaymentLinkService
     */
    private $paymentLinkService;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param Template\Context $context
     * @param PaymentLinkService $paymentLinkService
     * @param OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PaymentLinkService $paymentLinkService,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->paymentLinkService = $paymentLinkService;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
    }

    /**
     * Get payment link by order ID.
     *
     * @param int $orderId
     * @return bool|string
     */
    public function getPaymentLink($orderId)
    {
        $paymentLink = $this->paymentLinkService->getPaymentLinkByOrderId($orderId);
        return $paymentLink ? $paymentLink->getLink() : false;
    }
}
