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
            ['value' => 'future', 'label' => __('Future')],
            ['value' => 'canceled', 'label'  => __('Canceled')],
            ['value' => 'expired', 'label'  => __('Expired')]
        ];
    }
}
