<?php

namespace Vindi\Payment\Block\Onepage;


use Magento\Checkout\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Vindi\Payment\Api\ConfigurationInterface;

class Pix extends Template
{

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @param ConfigurationInterface $configuration
     * @param Session $checkoutSession
     * @param Context $context
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        ConfigurationInterface $configuration,
        Session $checkoutSession,
        Context $context,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->configuration = $configuration;
        $this->json = $json;
    }

    /**
     * @return bool
     */
    public function canShowPix()
    {
        return $this->getOrder()->getPayment()->getMethod() === \Vindi\Payment\Model\Payment\Pix::CODE;
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
        return $this->configuration->getQrCodeWarningMessage();
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
}
