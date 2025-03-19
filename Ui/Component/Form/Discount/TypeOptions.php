<?php

namespace Vindi\Payment\Ui\Component\Form\Discount;

class TypeOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Get options for discount types.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'percentage', 'label' => __('Percentage')],
            ['value' => 'amount', 'label' => __('Fixed Amount')],
            ['value' => 'quantity', 'label' => __('Quantity')],
        ];
    }
}
