<?php
namespace Vindi\Payment\Block\Adminhtml\Subscription\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;

/**
 * Class AddDiscountButton
 * Provides the "Add Discount" button in the Subscription Edit form.
 */
class AddDiscountButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * AddDiscountButton constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Get the configuration for the "Add Discount" button.
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        $subscriptionId = $this->getSubscriptionId();

        if ($subscriptionId) {
            $data = [
                'label' => __('Add Discount'),
                'class' => 'add primary',
                'on_click' => sprintf("location.href = '%s';", $this->getAddDiscountUrl()),
                'sort_order' => 5,
            ];
        }

        return $data;
    }

    /**
     * Get the current Subscription ID from the request.
     *
     * @return int|null
     */
    protected function getSubscriptionId()
    {
        return $this->context->getRequest()->getParam('id');
    }

    /**
     * Get the URL for the Add Discount action.
     *
     * @return string
     */
    protected function getAddDiscountUrl()
    {
        return $this->context->getUrlBuilder()->getUrl('vindi_payment/subscription/adddiscount', ['id' => $this->getSubscriptionId()]);
    }
}
