<?php

namespace Vindi\Payment\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class BillingTriggerTypeWithoutDayOfMount
 * @package Vindi\Payment\Model\Config\Source
 */
class BillingTriggerTypeWithoutDayOfMount extends AbstractSource
{
    const BEGINNING_OF_PERIOD = 'beginning_of_period';
    const END_OF_PERIOD = 'end_of_period';

    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('Beginning of Period'), 'value' => self::BEGINNING_OF_PERIOD],
                ['label' => __('End of Period'), 'value' => self::END_OF_PERIOD]
            ];
        }

        return $this->_options;
    }
}
