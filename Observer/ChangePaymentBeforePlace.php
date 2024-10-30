<?php

declare(strict_types=1);

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

namespace Vindi\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vindi\Payment\Helper\Data as VindiHelper;

class ChangePaymentBeforePlace implements ObserverInterface
{
    /**
     * @var VindiHelper
     */
    private VindiHelper $helper;

    /**
     * @param VindiHelper $helper
     */
    public function __construct(
        VindiHelper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $paymentMethod = $order->getPayment()->getMethod();

        if ($order->getData() && in_array($paymentMethod, $this->helper->getAllowedMethods())) {
            $order->getPayment()->setMethod('vindi_vr_payment_link_' . $paymentMethod);
        }
    }
}
