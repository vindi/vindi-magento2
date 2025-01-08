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
                'code' => 'MC',
                'label' => __('mastercard')
            ],
            [
                'value' => 'vi.png',
                'code' => 'VI',
                'label' => __('visa')
            ],
            [
                'value' => 'ae.png',
                'code' => 'AE',
                'label' => __('american_express')
            ],
            [
                'value' => 'elo.png',
                'code' => 'ELO',
                'label' => __('elo')
            ],
            [
                'value' => 'hc.png',
                'code' => 'HC',
                'label' => __('hipercard')
            ],
            [
                'value' => 'dn.png',
                'code' => 'DN',
                'label' => __('diners_club')
            ],
            [
                'value' => 'jcb.png',
                'code' => 'JCB',
                'label' => __('jcb')
            ],
        ];
    }
}
