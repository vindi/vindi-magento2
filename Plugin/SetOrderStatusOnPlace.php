<?php

namespace Vindi\Payment\Plugin;

class SetOrderStatusOnPlace
{
    public function afterPlace(\Magento\Sales\Model\Order\Payment $subject, $result)
    {
        if ($subject->getMethod() == \Vindi\Payment\Model\Payment\BankSlip::CODE) {
            $order = $subject->getOrder();
            $order->setState('new')
                ->setStatus('pending');
        }
        return $result;
    }
}