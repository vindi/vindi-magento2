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
use Psr\Log\LoggerInterface;
use DateTime;

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
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param PixConfigurationInterface $pixConfiguration
     * @param Session $checkoutSession
     * @param Context $context
     * @param Json $json
     * @param ResourceConnection $resourceConnection
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        PixConfigurationInterface $pixConfiguration,
        Session $checkoutSession,
        Context $context,
        Json $json,
        ResourceConnection $resourceConnection,
        TimezoneInterface $timezone,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->pixConfiguration = $pixConfiguration;
        $this->json = $json;
        $this->resourceConnection = $resourceConnection;
        $this->timezone = $timezone;
        $this->logger = $logger;
    }

    /**
     * Checks if the payment method is BankSlipPix and can show the BankSlipPix QR code
     *
     * @return bool
     */
    public function canShowBankSlipPix()
    {
        return $this->getOrder()->getPayment()->getMethod() === \Vindi\Payment\Model\Payment\BankSlipPix::CODE;
    }

    /**
     * Returns the BankSlipPix QR code path
     *
     * @return string[]
     */
    public function getQrCodePix()
    {
        return $this->getOrder()->getPayment()->getAdditionalInformation('qrcode_path');
    }

    /**
     * Returns the BankSlipPix QR code warning message
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
     * Returns the original BankSlipPix QR code path serialized in JSON format
     *
     * @return bool|string
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
     * Returns the print URL for the bank slip
     *
     * @return string
     */
    public function getBankslipPrintUrl()
    {
        return $this->getOrder()->getPayment()->getAdditionalInformation('print_url');
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

            $startAt = new DateTime($result);
            $startAt = $startAt->format('d/m/Y');

            return $result ? $startAt : null;
        } catch (\Exception $e) {
            $this->logger->error(__('Error fetching next billing date: %1', $e->getMessage()));
            return null;
        }
    }
}
