<?php

/**
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Vindi
 * @package     Vindi_Payment
 *
 */

declare(strict_types=1);

namespace Vindi\Payment\Plugin\Model\Service;

use Vindi\Payment\Model\PaymentLinkService;
use Vindi\Payment\Helper\Data as VindiHelper;

class OrderService
{
    /**
     * @var PaymentLinkService
     */
    private PaymentLinkService $paymentLinkService;

    /**
     * @var VindiHelper
     */
    private VindiHelper $helper;

    /**
     * @param PaymentLinkService $paymentLinkService
     * @param VindiHelper $helper
     */
    public function __construct(
        PaymentLinkService $paymentLinkService,
        VindiHelper $helper
    ) {
        $this->paymentLinkService = $paymentLinkService;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Sales\Model\Service\OrderService $subject
     * @param $result
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterPlace(\Magento\Sales\Model\Service\OrderService $subject, $result)
    {
        $paymentMethod = str_replace('vindi_vr_payment_link_', '', $result->getPayment()->getMethod());

        if (in_array($paymentMethod, $this->helper->getAllowedMethods())) {
            $this->paymentLinkService->createPaymentLink($result->getId(), $paymentMethod);
            $this->paymentLinkService->sendPaymentLinkEmail($result->getId());
        }

        return $result;
    }
}

