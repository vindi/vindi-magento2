<?php
namespace Vindi\Payment\Model\Config\Source\Subscription;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 * @package Vindi\Payment\Model\Config\Source\Subscription

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
            ['value' => 'future', 'label' => __('future')],
            ['value' => 'canceled', 'label'  => __('canceled')],
            ['value' => 'expired', 'label'  => __('expired')]
        ];
    }
}
