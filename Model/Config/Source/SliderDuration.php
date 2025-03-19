<?php
namespace Vindi\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class SliderTypes
 * @package Cedran\CrudAdmin\Model\Config\Source
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class SliderDuration implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = [
            'label' => __('Permanent'),
            'value' => '',
        ];

        $options[] = [
            'label' => __('Temporary'),
            'value' => 'select',
        ];

        return $options;
    }
}
