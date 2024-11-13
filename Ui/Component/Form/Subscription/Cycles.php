<?php

namespace Vindi\Payment\Ui\Component\Form\Subscription;

use Magento\Framework\Data\OptionSourceInterface;

class Cycles implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => '-1', 'label' => __('Permanent')],
        ];

        for ($i = 1; $i <= 24; $i++) {
            $options[] = ['value' => (string)$i, 'label' => __('Temporary for %1 period(s)', $i)];
        }

        return $options;
    }
}
