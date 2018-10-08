<?php

namespace Vindi\Payment\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Vindi\Payment\Model\Config\Source\Mode;

class Data extends AbstractHelper
{
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {

        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }

    public function getCreditCardConfig($field, $group = 'vindi')
    {
        return $this->scopeConfig->getValue(
            'payment/' . $group . '/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getModuleGeneralConfig($field)
    {
        return $this->scopeConfig->getValue(
            'vindiconfiguration/general/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isInstallmentsAllowedInStore()
    {
        return $this->getCreditCardConfig('allow_installments');
    }

    public function getMaxInstallments()
    {
        return $this->getCreditCardConfig('max_installments');
    }

    public function getMinInstallmentsValue()
    {
        return $this->getCreditCardConfig('min_installment_value');
    }

    public function getShouldVerifyProfile()
    {
        return $this->getCreditCardConfig('verify_method');
    }

    public function getWebhookKey()
    {
        return $this->getModuleGeneralConfig('webhook_key');
    }

    public function getMode()
    {
        return $this->getModuleGeneralConfig('mode');
    }

    public function getOrderStatus()
    {
        return $this->getCreditCardConfig('order_status');
    }

    public function getBaseUrl()
    {
        if ($this->getMode() == Mode::PRODUCTION_MODE) {
            return "https://app.vindi.com.br/api/v1/";
        }
        return "https://sandbox-app.vindi.com.br/api/v1/";
    }
}
