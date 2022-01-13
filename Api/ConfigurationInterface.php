<?php

namespace Vindi\Payment\Api;


use Magento\Store\Model\ScopeInterface;

interface ConfigurationInterface
{

    const PATH_QR_CODE_WARNING_MESSAGE = 'checkout/vindi_pix/qr-code-warning-message';

    /**
     * @param string $scopeType
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getQrCodeWarningMessage(string $scopeType = ScopeInterface::SCOPE_STORE, string $scopeCode = null);
}
