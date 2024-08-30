<?php

namespace Vindi\Payment\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Interval
 * @package Vindi\Payment\Model\Config\Source
 */
class Duration extends AbstractSource
{
    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('defined'), 'value'   => __('Temporary')],
                ['label' => __('undefined'), 'value' => __('Indefinitely')]
            ];
        }

        return $this->_options;
    }

    /**
     * Get a text for option value
     * @param string|integer $value
     * @return string|bool
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'defined', 'label'   => __('Temporary')],
            ['value' => 'undefined', 'label' => __('Indefinitely')]
        ];
    }
}
