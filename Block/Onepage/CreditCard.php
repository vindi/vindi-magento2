<?php

namespace Vindi\Payment\Block\Onepage;

use Magento\Checkout\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Vindi\Payment\Api\CcConfigurationInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use DateTime;

/**
 * Class CreditCard
 * @package Vindi\Payment\Block\Onepage
 */
class CreditCard extends Template
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var CcConfigurationInterface
     */
    protected $ccConfiguration;

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
     * CreditCard constructor.
     * @param CcConfigurationInterface $ccConfiguration
     * @param Session $checkoutSession
     * @param Context $context
     * @param Json $json
     * @param ResourceConnection $resourceConnection
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        CcConfigurationInterface $ccConfiguration,
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
        $this->ccConfiguration = $ccConfiguration;
        $this->json = $json;
        $this->resourceConnection = $resourceConnection;
        $this->timezone = $timezone;
        $this->logger = $logger;
    }

    /**
     * Checks if the payment method is Credit Card and can show the information
     *
     * @return bool
     */
    public function canShowCc()
    {
        $order = $this->getOrder();

        if ($order && $order->getPayment()) {
            $payment = $order->getPayment();
            $isCreditCard = $payment->getMethod() === \Vindi\Payment\Model\Payment\CreditCard::CODE;

            if (!$isCreditCard) {
                return false;
            }

            $vindiBillId  = $order->getData('vindi_bill_id');

            if ($vindiBillId == false || $vindiBillId == null || empty($vindiBillId) || $vindiBillId < 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the information message displayed on onepage success
     *
     * @return string
     */
    public function getInfoMessageOnepageSuccess()
    {
        return $this->ccConfiguration->getInfoMessageOnepageSuccess();
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

            $startAt = new DateTime($result);
            $startAt = $startAt->format('d/m/Y');

            return $result ? $startAt : null;
        } catch (\Exception $e) {
            $this->logger->error(__('Error fetching next billing date: %1', $e->getMessage()));
            return null;
        }
    }
}

