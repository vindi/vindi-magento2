<?php

namespace Vindi\Payment\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class BillingTriggerDaysOfTheMonth
 * @package Vindi\Payment\Model\Config\Source
 */
class BillingTriggerDaysOfTheMonth extends AbstractSource
{
    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = $this->getRangeOptions();
        }

        return $this->_options;
    }

    /**
     * @return array
     */
    private function getRangeOptions()
    {
        $range =  [];
        for ($number = 1; $number <= 30; $number++) {
            $range[] = [
                'label' => __('Day %1', $number),
                'value' => $number
            ];
        }

        return $range;
    }
}
