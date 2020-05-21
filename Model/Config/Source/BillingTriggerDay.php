<?php

namespace Vindi\Payment\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class BillingTriggerDay
 * @package Vindi\Payment\Model\Config\Source
 */
class BillingTriggerDay extends AbstractSource
{
    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = $this->getRangeOptionsBefore();

            $this->_options = array_merge($this->_options, [
                ['label' => __('1 day before'), 'value' => -1],
                ['label' => __('Exactly on the Day'), 'value' => 0],
                ['label' => __('1 day later'), 'value' => 1]
            ]);

            $this->_options = array_merge($this->_options, $this->getRangeOptionsLater());
        }

        return $this->_options;
    }

    /**
     * @return array
     */
    private function getRangeOptionsBefore()
    {
        $range =  [];
        for ($number = -25; $number < -1; $number++) {
            $range[] = [
                'label' => __('%1 days before', ($number * -1)),
                'value' => $number
            ];
        }

        return $range;
    }

    /**
     * @return array
     */
    public function getRangeOptionsLater()
    {
        $range =  [];
        for ($number = 2; $number <= 30; $number++) {
            $range[] = [
                'label' => __('%1 days later', $number),
                'value' => $number
            ];
        }

        return $range;
    }
}
