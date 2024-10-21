<?php

declare(strict_types=1);

namespace Vindi\Payment\Block\Adminhtml\Order;

use \Magento\Backend\Block\Template;
use \Magento\Backend\Block\Template\Context;
use Vindi\Payment\Model\PaymentLinkService;

class LinkField extends Template
{
    const VINDI_PAYMENT_LINK = 'vindi_vr_payment_link_';

    /**
     * @var PaymentLinkService
     */
    private PaymentLinkService $paymentLinkService;

    /**
     * @param Context $context
     * @param PaymentLinkService $paymentLinkService
     * @param array $data
     */
    public function __construct(
        Context $context,
        PaymentLinkService $paymentLinkService,
        array $data = []
    ) {
        $this->paymentLinkService = $paymentLinkService;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }

    /**
     * @return string
     */
    public function getPaymentLink()
    {
        $paymentLinkData = $this->paymentLinkService->getPaymentLink($this->getOrderId());
        return $paymentLinkData->getLink() ?? '';
    }

    /**
     * @return string|null
     */
    public function getPaymentMethod()
    {
        $order = $this->paymentLinkService->getOrderByOrderId($this->getOrderId());

        if ($order->getData()) {
            return $order->getPayment()->getMethod();
        }
        return null;
    }

    /**
     * Check if the payment link status is "processed"
     *
     * @return bool
     */
    public function isLinkPaid(): bool
    {
        $paymentLink = $this->paymentLinkService->getPaymentLink($this->getOrderId());
        return $paymentLink->getStatus() === 'processed';
    }

    /**
     * Check if the payment link is expired
     *
     * @return bool
     */
    public function isLinkExpired(): bool
    {
        $paymentLink = $this->paymentLinkService->getPaymentLink($this->getOrderId());
        return $paymentLink->getStatus() === 'expired';
    }
}
