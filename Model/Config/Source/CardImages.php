<?php

namespace Vindi\Payment\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class CardImages implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'mc.png',
                'label' => __('mastercard')
            ],
            [
                'value' => 'vi.png',
                'label' => __('visa')
            ],
            [
                'value' => 'ae.png',
                'label' => __('american_express')
            ]
        ];
    }
}
