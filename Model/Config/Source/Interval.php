<?php

namespace Vindi\Payment\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Interval
 * @package Vindi\Payment\Model\Config\Source
 */
class Interval extends AbstractSource
{
    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('month(s)'), 'value' => 'months'],
                ['label' => __('day(s)'), 'value' => 'days']
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
            ['value' => 'days', 'label' => __('Day')],
            ['value' => 'months', 'label' => __('Month')]
        ];
    }
}
