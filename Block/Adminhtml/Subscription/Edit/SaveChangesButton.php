<?php

namespace Vindi\Payment\Block\Adminhtml\Subscription\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SaveChangesButton
 * @package Vindi\Payment\Block\Adminhtml\Subscription\Edit
 */
class SaveChangesButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save Changes'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 80,
        ];
    }
}