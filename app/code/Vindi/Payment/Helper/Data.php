<?php

namespace Vindi\Payment\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    public function getModuleConfig($field, $group = 'vindi')
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        return $scopeConfig->getValue('payment/' . $group . '/' . $field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isInstallmentsAllowedInStore()
    {
        return $this->getModuleConfig('allow_installments');
    }

    public function getMaxInstallments()
    {
        return $this->getModuleConfig('max_installments');
    }

    public function getMinInstallmentsValue()
    {
        return $this->getModuleConfig('min_installment_value');
    }

    public function getShouldVerifyProfile()
    {
        return $this->getModuleConfig('verify_method');
    }

    public function getWebhookKey()
    {
        return $this->getModuleConfig('webhook_key');
    }
}