<?php

namespace Vindi\Payment\Block\Info;


use Magento\Backend\Block\Template\Context;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Block\Info;
use Vindi\Payment\Api\PixConfigurationInterface;
use Vindi\Payment\Model\Payment\PaymentMethod;

/**
 * Class Pix
 *
 * @package Vindi\Payment\Block\Info
 */
class Pix extends Info
{

    /**
     * @var string
     */
    protected $_template = 'Vindi_Payment::info/pix.phtml';

    /**
     * @var Data
     */
    protected $_currency;

    /**
     * @var PixConfigurationInterface
     */
    protected $pixConfiguration;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @param PaymentMethod $paymentMethod
     * @param Data $currency
     * @param Context $context
     * @param PixConfigurationInterface $pixConfiguration
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        PaymentMethod $paymentMethod,
        Data $currency,
        Context $context,
        PixConfigurationInterface $pixConfiguration,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->paymentMethod = $paymentMethod;
        $this->_currency = $currency;
        $this->pixConfiguration = $pixConfiguration;
        $this->json = $json;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrder()
    {
        return $this->getInfo()->getOrder();
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function canShowPixInfo()
    {
        return $this->getOrder()->getPayment()->getMethod() === \Vindi\Payment\Model\Payment\Pix::CODE;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQrcodeOriginalPath()
    {
        $qrcodeOriginalPath = $this->getOrder()->getPayment()->getAdditionalInformation('qrcode_original_path');
        return $this->json->serialize($qrcodeOriginalPath);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDaysToKeepWaitingPayment()
    {
        $timestamp = strtotime($this->getOrder()->getPayment()->getAdditionalInformation('max_days_to_keep_waiting_payment'));
        return  date('d/m/Y H:m:s', $timestamp);
    }
}
