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
                ['label' => __('defined'), 'value'   => 'Por tempo definido'],
                ['label' => __('undefined'), 'value' => 'Por tempo indefinido']
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
            ['value' => 'defined', 'label'   => __('Por tempo definido')],
            ['value' => 'undefined', 'label' => __('Por tempo indefinido')]
        ];
    }
}
