<?php
namespace Vindi\Payment\Model\Config\Source\Plan;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'active', 'label'   => __('Active')],
            ['value' => 'inactive', 'label' => __('Inactive')],
            ['value' => 'deleted', 'label'  => __('Deleted')]
        ];
    }
}
