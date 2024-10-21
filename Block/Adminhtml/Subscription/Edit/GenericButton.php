<?php

namespace Vindi\Payment\Block\Adminhtml\Subscription\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

/**
 * Class GenericButton
 *
 * @package Vindi\Payment\Block\Adminhtml\Subscription\Edit
 */
abstract class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * GenericButton constructor.
     *
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(Context $context, Registry $registry)
    {
        $this->context = $context;
        $this->registry = $registry;
    }

    /**
     * Return Subscription ID
     *
     * @return int|null
     */
    public function getModelId()
    {
        return $this->registry->registry('vindi_payment_subscription_id') ?: $this->context->getRequest()->getParam('id');
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}

