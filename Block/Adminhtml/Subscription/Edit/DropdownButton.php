<?php

namespace Vindi\Payment\Block\Adminhtml\Subscription\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;

/**
 * Class DropdownButton
 * Provides a dropdown button with multiple options in the Subscription Edit form.
 */
class DropdownButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * DropdownButton constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Get the configuration for the dropdown button.
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Add'),
            'class' => 'actions-dropdown',
            'class_name' => 'Magento\Ui\Component\Control\SplitButton',
            'options' => $this->getDropdownOptions(),
            'sort_order' => 20,
        ];
    }

    /**
     * Get options for the dropdown menu.
     *
     * @return array
     */
    private function getDropdownOptions()
    {
        return [
            [
                'id' => 'add_item',
                'label' => __('Add Item'),
                'onclick' => sprintf("location.href = '%s';", $this->getAddItemUrl()),
            ],
            [
                'id' => 'add_discount',
                'label' => __('Add Discount'),
                'onclick' => sprintf("location.href = '%s';", $this->getAddDiscountUrl()),
            ],
        ];
    }

    /**
     * Get the URL for the Add Discount action.
     *
     * @return string
     */
    private function getAddDiscountUrl()
    {
        return $this->context->getUrlBuilder()->getUrl('vindi_payment/subscription/adddiscount', ['id' => $this->getSubscriptionId()]);
    }

    /**
     * Get the URL for the Add Item action.
     *
     * @return string
     */
    private function getAddItemUrl()
    {
        return $this->context->getUrlBuilder()->getUrl('vindi_payment/subscription/add', ['id' => $this->getSubscriptionId()]);
    }

    /**
     * Get the current Subscription ID from the request.
     *
     * @return int|null
     */
    private function getSubscriptionId()
    {
        return $this->context->getRequest()->getParam('id');
    }
}
