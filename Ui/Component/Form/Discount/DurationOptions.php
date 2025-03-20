<?php

namespace Vindi\Payment\Ui\Component\Form\Discount;

class DurationOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Get options for durations.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'permanent', 'label' => __('Permanent')],
            ['value' => 'temporary', 'label' => __('Temporary')],
        ];
    }
}
