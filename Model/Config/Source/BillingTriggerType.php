<?php

namespace Vindi\Payment\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class BillingTriggerType
 * @package Vindi\Payment\Model\Config\Source
 */
class BillingTriggerType extends AbstractSource
{
    const BEGINNING_OF_PERIOD = 'beginning_of_period';
    const END_OF_PERIOD = 'end_of_period';
    const DAY_OF_MONTH = 'day_of_month';

    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('Begging of Period'), 'value' => self::BEGINNING_OF_PERIOD],
                ['label' => __('End of Period'), 'value' => self::END_OF_PERIOD],
                ['label' => __('Day of Month'), 'value' => self::DAY_OF_MONTH]
            ];
        }

        return $this->_options;
    }
}
