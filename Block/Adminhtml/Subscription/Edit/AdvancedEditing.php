<?php

namespace Vindi\Payment\Block\Adminhtml\Subscription\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Vindi\Payment\Helper\Data;
use Vindi\Payment\Model\Config\Source\Mode;

/**
 * Class AdvancedEditing
 * @package Vindi\Payment\Block\Adminhtml\Subscription\Edit
 */
class AdvancedEditing extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * AdvancedEditing constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $helperData
    ) {
        parent::__construct($context, $registry);  // Passing both context and registry to the parent constructor
        $this->helperData = $helperData;
        $this->registry = $registry;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Advanced Editing'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'save',
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
        $id = $this->registry->registry('vindi_payment_subscription_id');
        $prefix = 'sandbox-';
        if ($this->helperData->getMode() == Mode::PRODUCTION_MODE) {
            $prefix = '';
        }

        return 'https://' . $prefix . 'app.vindi.com.br/admin/subscriptions/' . $id;
    }
}
