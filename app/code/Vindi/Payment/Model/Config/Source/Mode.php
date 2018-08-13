<?php

namespace Vindi\Payment\Model\Config\Source;


use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\Method\AbstractMethod;

class Mode implements ArrayInterface
{
    const PRODUCTION_MODE = 1;
    const SANDBOX_MODE = 2;

    public function toOptionArray()
    {
        return [
            [
                'value' =>  self::PRODUCTION_MODE,
                'label' =>__('Production')
            ],
            [
                'value' => self::SANDBOX_MODE,
                'label' => __('Sandbox')
            ]
        ];
    }
}