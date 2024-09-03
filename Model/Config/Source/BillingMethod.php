<?php

namespace Vindi\Payment\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Interval
 * @package Vindi\Payment\Model\Config\Source
 */
class BillingMethod extends AbstractSource
{
    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('based_on_period'), 'value'   => __('Baseado no período')],
                ['label' => __('day_of_month'), 'value' => __('Dia específico')]
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
            ['value' => 'based_on_period', 'label'   => __('Baseado no período')],
            ['value' => 'day_of_month', 'label' => __('Dia específico')]
        ];
    }
}
