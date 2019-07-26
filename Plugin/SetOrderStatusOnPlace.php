<?php

namespace Vindi\Payment\Plugin;

use Vindi\Payment\Helper\Data;

class SetOrderStatusOnPlace
{
    /**
     * @var Data
     */
    private $data;

    /**
     * SetOrderStatusOnPlace constructor.
     * @param Data $data
     */
    public function __construct(
        Data $data
    ) {
        $this->data = $data;
    }

    public function afterPlace(\Magento\Sales\Model\Order\Payment $subject, $result)
    {
        if ($subject->getMethod() == \Vindi\Payment\Model\Payment\BankSlip::CODE) {
            $order = $subject->getOrder();
            $order->setState('new')
                ->setStatus($this->data->getOrderStatus());
        }
        return $result;
    }
}