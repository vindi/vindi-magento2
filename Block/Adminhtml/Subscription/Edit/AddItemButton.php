<?php

namespace Vindi\Payment\Block\Adminhtml\Subscription\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;

class AddItemButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Retrieve button-specified settings
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Add Item'),
            'on_click' => sprintf("location.href = '%s';", $this->getAddItemUrl()),
            'class' => 'add primary',
            'sort_order' => 10,
        ];
    }

    /**
     * Get URL for Add Item action, including the current subscription ID
     *
     * @return string
     */
    public function getAddItemUrl()
    {
        $id = $this->getSubscriptionId();
        return $this->context->getUrlBuilder()->getUrl('vindi_payment/subscription/add', ['id' => $id]);
    }

    /**
     * Retrieve current subscription ID from request
     *
     * @return int|null
     */
    private function getSubscriptionId()
    {
        return $this->context->getRequest()->getParam('id');
    }
}
