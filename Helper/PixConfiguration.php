<?php

namespace Vindi\Payment\Helper;


use Magento\Store\Model\ScopeInterface;
use Vindi\Payment\Api\PixConfigurationInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class PixConfiguration extends AbstractHelper implements PixConfigurationInterface
{

    /**
     * @inheritDoc
     */
    public function getInfoMessage(string $scopeType = ScopeInterface::SCOPE_STORE, string $scopeCode = null)
    {
        $result = $this->scopeConfig->getValue(static::PATH_INFO_MESSAGE, $scopeType, $scopeCode);
        return $result ?: '';
    }

    /**
     * @inheritDoc
     */
    public function getInfoMessageOnepageSuccess(string $scopeType = ScopeInterface::SCOPE_STORE, string $scopeCode = null)
    {
        $result = $this->scopeConfig->getValue(static::PATH_INFO_MESSAGE_ONEPAGE_SUCCESS, $scopeType, $scopeCode);
        return $result ?: '';
    }

    /**
     * @inheritDoc
     */
    public function getQrCodeWarningMessage(string $scopeType = ScopeInterface::SCOPE_STORE, string $scopeCode = null)
    {
        $result = $this->scopeConfig->getValue(static::PATH_QR_CODE_WARNING_MESSAGE, $scopeType, $scopeCode);
        return $result ?: '';
    }
}
