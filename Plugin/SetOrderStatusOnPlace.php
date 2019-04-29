<?php

namespace Vindi\Payment\Plugin;

use Magento\Sales\Model\Order;

class SetOrderStatusOnPlace
{
    public function afterPlace(\Magento\Sales\Model\Order\Payment $subject, $result)
    {
        if ($subject->getMethod() == \Vindi\Payment\Model\Payment\BankSlip::CODE) {
            $order = $subject->getOrder();
            $order->setState(Order::STATE_NEW)
                ->setStatus($subject->getMethodInstance()->getConfigData('order_status'));
        }
        return $result;
    }
}