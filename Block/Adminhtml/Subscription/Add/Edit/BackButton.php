<?php

namespace Vindi\Payment\Block\Adminhtml\Subscription\Add\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Vindi\Payment\Block\Adminhtml\Subscription\Edit\GenericButton;

/**
 * Class BackButton
 * @package Vindi\Payment\Block\Adminhtml\Subscription\Edit
 */
class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        if (!$this->getModelId()) {
            return $this->getUrl('*/*/');
        }

        return $this->getUrl('*/*/edit', ['id' => $this->getModelId()]);
    }
}
