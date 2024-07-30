<?php

declare(strict_types=1);

/**
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Vindi
 * @package     Vindi_Payment
 *
 */
namespace Vindi\Payment\Block\Custom;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Vindi\Payment\Helper\Data as Helper;
use Vindi\Payment\Model\PaymentLinkService;
use Magento\Store\Model\ScopeInterface;

class PaymentLinkSuccess extends Template
{
    /**
     * @var PaymentLinkService
     */
    private PaymentLinkService $paymentLinkService;

    /**
     * @var FormKey
     */
    private FormKey $formKey;

    /**
     * @var Helper
     */
    private Helper $helper;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param Context $context
     * @param PaymentLinkService $paymentLinkService
     * @param FormKey $formKey
     * @param Helper $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        PaymentLinkService $paymentLinkService,
        FormKey $formKey,
        Helper $helper,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->paymentLinkService = $paymentLinkService;
        $this->formKey = $formKey;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        return $this->paymentLinkService->getOrderByOrderId($this->getOrderId());
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->getOrder()->getPayment()->getMethod();
    }

    /**
     * @throws LocalizedException
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return string
     */
    public function getInstructions()
    {
        return $this->helper->getConfig('payment_link_instructions', $this->getPaymentMethod());
    }

    /**
     * Get store configuration value by path
     *
     * @param string $path
     * @param string|null $scope
     * @return string|null
     */
    public function getStoreConfig(string $path, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($path, $scope);
    }
}
