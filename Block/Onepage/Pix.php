<?php

namespace Vindi\Payment\Block\Onepage;

use Magento\Checkout\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Vindi\Payment\Api\PixConfigurationInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Pix
 * @package Vindi\Payment\Block\Onepage
 */
class Pix extends Template
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
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * Pix constructor.
     * @param PixConfigurationInterface $pixConfiguration
     * @param Session $checkoutSession
     * @param Context $context
     * @param Json $json
     * @param ResourceConnection $resourceConnection
     * @param TimezoneInterface $timezone
     * @param array $data
     */
    public function __construct(
        PixConfigurationInterface $pixConfiguration,
        Session $checkoutSession,
        Context $context,
        Json $json,
        ResourceConnection $resourceConnection,
        TimezoneInterface $timezone,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->pixConfiguration = $pixConfiguration;
        $this->json = $json;
        $this->resourceConnection = $resourceConnection;
        $this->timezone = $timezone;
    }

    /**
     * Checks if the payment method is Pix and can show the Pix QR code
     *
     * @return bool
     */
    public function canShowPix()
    {
        $order = $this->getOrder();
        if ($order && $order->getPayment()) {
            return $order->getPayment()->getMethod() === \Vindi\Payment\Model\Payment\Pix::CODE;
        }
        return false;
    }

    /**
     * Returns the Pix QR code path
     *
     * @return string
     */
    public function getQrCodePix()
    {
        return $this->getOrder()->getPayment()->getAdditionalInformation('qrcode_path') ?? '';
    }

    /**
     * Returns the Pix QR code warning message
     *
     * @return string
     */
    public function getQrCodeWarningMessage()
    {
        return $this->pixConfiguration->getQrCodeWarningMessage();
    }

    /**
     * Returns the information message displayed on onepage success
     *
     * @return string
     */
    public function getInfoMessageOnepageSuccess()
    {
        return $this->pixConfiguration->getInfoMessageOnepageSuccess();
    }

    /**
     * Returns the original Pix QR code path serialized in JSON format
     *
     * @return string
     */
    public function getQrcodeOriginalPath()
    {
        $qrcodeOriginalPath = $this->getOrder()->getPayment()->getAdditionalInformation('qrcode_original_path');
        return $this->json->serialize($qrcodeOriginalPath);
    }

    /**
     * Retrieves the last real order from the checkout session
     *
     * @return Order
     */
    protected function getOrder(): Order
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * Returns the next billing date of the subscription
     *
     * @return string|null
     */
    public function getNextBillingDate()
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $subscriptionOrdersTable = $this->resourceConnection->getTableName('vindi_subscription_orders');
            $subscriptionTable = $this->resourceConnection->getTableName('vindi_subscription');

            $select = $connection->select()
                ->from(['so' => $subscriptionOrdersTable], [])
                ->joinInner(
                    ['s' => $subscriptionTable],
                    'so.subscription_id = s.id',
                    ['next_billing_at']
                )
                ->where('so.order_id = ?', $this->getOrder()->getId());

            $result = $connection->fetchOne($select);
            return $result ? $this->timezone->formatDate($result, \IntlDateFormatter::SHORT, false) : null;
        } catch (\Exception $e) {
            $this->logger->error(__('Error fetching next billing date: %1', $e->getMessage()));
            return null;
        }
    }
}
