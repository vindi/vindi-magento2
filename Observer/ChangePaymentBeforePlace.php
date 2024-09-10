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
 *
 */

namespace Vindi\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;


class ChangePaymentBeforePlace implements ObserverInterface
{
    /**
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getData() && str_contains($order->getPayment()->getMethod(), 'vindi')) {
            $order->getPayment()->setMethod('vindi_payment_link_' . $order->getPayment()->getMethod());
        }
    }
}
