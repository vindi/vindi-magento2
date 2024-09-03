<?php

namespace Vindi\Payment\Block\Onepage;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use DateTime;

class Bankslip extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Session $checkoutSession
     * @param Context $context
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Session $checkoutSession,
        Context $context,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * Retrieves the last real order from the checkout session
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * Checks if the payment method is BankSlip and can show the BankSlip
     *
     * @return bool
     */
    public function canShowBankslip()
    {
        $order = $this->getOrder();
        if ($order->getPayment()->getMethod() === \Vindi\Payment\Model\Payment\BankSlip::CODE) {
            return true;
        }

        return false;
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

