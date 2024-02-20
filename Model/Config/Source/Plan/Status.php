<?php
namespace Vindi\Payment\Model\Config\Source\Plan;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 * @package Vindi\Payment\Model\Config\Source\Plan
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class Status implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'active', 'label'   => __('Active')],
            ['value' => 'inactive', 'label' => __('Inactive')],
            ['value' => 'deleted', 'label'  => __('Deleted')]
        ];
    }
}
