<?php

namespace Vindi\Payment\Block\Onepage;


use Magento\Checkout\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Vindi\Payment\Api\PixConfigurationInterface;

class Bankslippix extends Template
{

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var PixConfigurationInterface
     */
    protected $pixConfiguration;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @param PixConfigurationInterface $pixConfiguration
     * @param Session $checkoutSession
     * @param Context $context
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        PixConfigurationInterface $pixConfiguration,
        Session $checkoutSession,
        Context $context,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->pixConfiguration = $pixConfiguration;
        $this->json = $json;
    }

    /**
     * @return bool
     */
    public function canShowBankSlipPix()
    {
        return $this->getOrder()->getPayment()->getMethod() === \Vindi\Payment\Model\Payment\BankSlipPix::CODE;
    }

    /**
     * @return string[]
     */
    public function getQrCodePix()
    {
        return $this->getOrder()->getPayment()->getAdditionalInformation('qrcode_path');
    }

    /**
     * @return string
     */
    public function getQrCodeWarningMessage()
    {
        return $this->pixConfiguration->getQrCodeWarningMessage();
    }

    /**
     * @return string
     */
    public function getInfoMessageOnepageSuccess()
    {
        return $this->pixConfiguration->getInfoMessageOnepageSuccess();
    }

    /**
     * @return bool|string
     */
    public function getQrcodeOriginalPath()
    {
        $qrcodeOriginalPath = $this->getOrder()->getPayment()->getAdditionalInformation('qrcode_original_path');
        return $this->json->serialize($qrcodeOriginalPath);
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrder(): Order
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    public function getBankslipPrintUrl()
    {
        return $this->getOrder()->getPayment()->getAdditionalInformation('print_url');
    }
}
