<?php

namespace Vindi\Payment\Model\Config\Source;


use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\Method\AbstractMethod;

class PaymentAction implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => AbstractMethod::ACTION_AUTHORIZE,
                'label' => __('Authorize')
            ],
            [
                'value' =>  AbstractMethod::ACTION_AUTHORIZE_CAPTURE,
                'label' =>__('Authorize and Capture')
            ]
        ];
    }
}