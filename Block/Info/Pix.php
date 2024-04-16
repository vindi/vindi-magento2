<?php

namespace Vindi\Payment\Block\Info;


use Magento\Backend\Block\Template\Context;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
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
    protected $currency;

    /**
     * @var PixConfigurationInterface
     */
    protected $pixConfiguration;

    /** @var TimezoneInterface */
    protected $timezone;

    /**
     * @var Json
     */
    protected $json;

    protected $paymentMethod;

    public function __construct(
        PaymentMethod $paymentMethod,
        Data $currency,
        Context $context,
        PixConfigurationInterface $pixConfiguration,
        TimezoneInterface $timezone,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->paymentMethod = $paymentMethod;
        $this->currency = $currency;
        $this->pixConfiguration = $pixConfiguration;
        $this->timezone = $timezone;
        $this->json = $json;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBillId()
    {
        $order = $this->getOrder();
        $billId = $order->getVindiBillId() ?? null;

        return $billId;
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
     * Get reorder URL
     *
     * @param object $order
     * @return string
     */
    public function getReorderUrl()
    {
        $order = $this->getOrder();
        return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function canShowPixInfo()
    {
        $paymentMethod = $this->getOrder()->getPayment()->getMethod() === \Vindi\Payment\Model\Payment\Pix::CODE;
        $daysToPayment = $this->getMaxDaysToPayment();

        if (!$daysToPayment) {
            return true;
        }

        $timestampMaxDays = strtotime($daysToPayment);

        return $paymentMethod && $this->isValidToPayment($timestampMaxDays);
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
        $daysToPayment = $this->getMaxDaysToPayment();
        if (!$daysToPayment) {
            return null;
        }

        $timestampMaxDays = strtotime($daysToPayment);
        return date('d/m/Y H:m:s', $timestampMaxDays);
    }

    /**
     * @param $timestampMaxDays
     *
     * @return bool
     */
    protected function isValidToPayment($timestampMaxDays)
    {
        if (!$timestampMaxDays) {
            return false;
        }

        return $timestampMaxDays >= $this->timezone->scopeTimeStamp();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getMaxDaysToPayment(): string
    {
        return (string) $this->getOrder()->getPayment()->getAdditionalInformation('max_days_to_keep_waiting_payment');
    }
}
