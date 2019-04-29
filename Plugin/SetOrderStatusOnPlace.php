<?php

namespace Vindi\Payment\Plugin;

use Magento\Sales\Model\Order;

class SetOrderStatusOnPlace
{
    public function afterPlace(\Magento\Sales\Model\Order\Payment $subject, $result)
    {
        $order = $subject->getOrder();
        $order->setState(Order::STATE_NEW)
            ->setStatus($subject->getMethodInstance()->getConfigData('order_status'));
        return $result;
    }
}