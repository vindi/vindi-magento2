<?php
namespace Vindi\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class DiscountType
 * @package Vindi\Payment\Model\Config\Source
 */
class DiscountType implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Percentage'), 'value' => 'percentage'],
            ['label' => __('Amount '), 'value' => 'amount'],
            ['label' => __('Quantity'), 'value' => 'quantity'],
        ];
    }
}
