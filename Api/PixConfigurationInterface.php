<?php

namespace Vindi\Payment\Api;


use Magento\Store\Model\ScopeInterface;

interface PixConfigurationInterface
{

    const PATH_INFO_MESSAGE = 'checkout/vindi_pix/info_message';
    const PATH_INFO_MESSAGE_ONEPAGE_SUCCESS = 'checkout/vindi_pix/info_message_onepage_success';
    const PATH_QR_CODE_WARNING_MESSAGE = 'checkout/vindi_pix/qr_code_warning_message';

    /**
     * @param string $scopeType
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getInfoMessage(string $scopeType = ScopeInterface::SCOPE_STORE, string $scopeCode = null);

    /**
     * @param string $scopeType
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getInfoMessageOnepageSuccess(string $scopeType = ScopeInterface::SCOPE_STORE, string $scopeCode = null);

    /**
     * @param string $scopeType
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getQrCodeWarningMessage(string $scopeType = ScopeInterface::SCOPE_STORE, string $scopeCode = null);
}
