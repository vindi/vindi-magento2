<?php
namespace Vindi\Payment\Block\Adminhtml\Log\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Vindi\Payment\Block\Adminhtml\Log\Edit\GenericButton;

class BackButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    public function getBackUrl()
    {
        return $this->getUrl('vindi_payment/logs/index');
    }
}
