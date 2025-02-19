<?php

declare(strict_types=1);

namespace Vindi\Payment\Model;

use Vindi\Payment\Model\ResourceModel\PaymentLink\CollectionFactory as PaymentLinkCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Vindi\Payment\Api\PaymentLinkRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Vindi\Payment\Helper\Data;
use Vindi\Payment\Model\PaymentLinkFactory;

class PaymentLinkService
{
    /**
     * Payment link expiration time, 20 days (in hours)
     */
    public const LINK_EXPIRATION_TIME = 480;

    /**
     * Sales email config path
     */
    public const SALES_EMAIL = 'trans_email/ident_sales/email';

    /**
     * Sales name config path
     */
    public const SALES_NAME = 'trans_email/ident_sales/name';

    /**
     * Payment link template path
     */
    public const PAYMENT_LINK_TEMPLATE_PATH = 'vindiconfiguration/general/vindi_vr_payment_link_template';

    /**
     * @var PaymentLinkCollectionFactory
     */
    private $paymentLinkCollectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PaymentLinkFactory
     */
    private $paymentLinkFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var PaymentLinkRepositoryInterface
     */
    private $linkRepository;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var SendEmailService
     */
    private $sendEmailService;

    /**
     * @param PaymentLinkCollectionFactory $paymentLinkCollectionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param PaymentLinkFactory $paymentLinkFactory
     * @param Data $helper
     * @param PaymentLinkRepositoryInterface $linkRepository
     * @param DateTimeFactory $dateTimeFactory
     * @param SendEmailService $sendEmailService
     */
    public function __construct(
        PaymentLinkCollectionFactory $paymentLinkCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        PaymentLinkFactory $paymentLinkFactory,
        Data $helper,
        PaymentLinkRepositoryInterface $linkRepository,
        DateTimeFactory $dateTimeFactory,
        SendEmailService $sendEmailService
    ) {
        $this->paymentLinkCollectionFactory = $paymentLinkCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->paymentLinkFactory = $paymentLinkFactory;
        $this->helper = $helper;
        $this->linkRepository = $linkRepository;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->sendEmailService = $sendEmailService;
    }

    /**
     * @param string|int $orderId
     * @return mixed
     */
    public function getPaymentLink($orderId)
    {
        return $this->paymentLinkCollectionFactory->create()
            ->addFieldToFilter('order_id', $orderId)
            ->getFirstItem();
    }

    /**
     * @param string $hash
     * @return mixed
     */
    public function getPaymentLinkByHash(string $hash)
    {
        return $this->paymentLinkCollectionFactory->create()
            ->addFieldToFilter('link', ['like' => '%' . $hash . '%'])
            ->getFirstItem();
    }

    /**
     * @param string|int $orderId
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrderByOrderId($orderId)
    {
        return $this->orderRepository->get($orderId);
    }

    /**
     * @param string|int $orderId
     * @return bool
     */
    public function sendPaymentLinkEmail($orderId): bool
    {
        try {
            $paymentLink = $this->getPaymentLink($orderId);

            if (!$paymentLink || !$paymentLink->getId()) {
                $this->logger->error('Payment link not found for order ID: ' . $orderId);
                return false;
            }

            $order = $this->getOrderByOrderId($orderId);

            if ($paymentLink->getData()) {
                if ($this->isLinkExpired($paymentLink->getCreatedAt())) {
                    $paymentLink = $this->updatePaymentLink($paymentLink);
                }
            } else {
                $this->createPaymentLink($orderId, str_replace('vindi_vr_payment_link_', '', $order->getPayment()->getMethod()));
                $paymentLink = $this->getPaymentLink($orderId);
            }

            if (!$paymentLink || !$paymentLink->getId()) {
                $this->logger->error('Failed to generate payment link for order ID: ' . $orderId);
                return false;
            }

            $templateVars = [
                'customer_name'   => $order->getCustomerFirstname(),
                'payment_link'    => $paymentLink->getLink(),
                'order_increment' => $order->getIncrementId()
            ];
            $from = [
                'email' => $this->scopeConfig->getValue(self::SALES_EMAIL, ScopeInterface::SCOPE_STORE),
                'name' => $this->scopeConfig->getValue(self::SALES_NAME, ScopeInterface::SCOPE_STORE)
            ];

            $emailTemplateId = $this->scopeConfig->getValue(self::PAYMENT_LINK_TEMPLATE_PATH, ScopeInterface::SCOPE_STORE)
                ?: 'vindi_vr_payment_link_template';

            $this->sendEmailService->sendEmailTemplate($emailTemplateId, $order->getCustomerEmail(), $order->getCustomerFirstname(), $from, $templateVars);
            return true;
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            return false;
        }
    }

    /**
     * @param string|int $orderId
     * @return string
     */
    public function createPaymentLink($orderId, string $paymentMethod): string
    {
        $link = '';
        try {
            $paymentLink = $this->getPaymentLink($orderId);
            $order = $this->getOrderByOrderId($orderId);
            if (!$paymentLink->getData()) {
                $paymentLink = $this->paymentLinkFactory->create();
            }

            $order = $this->getOrderByOrderId($orderId);
            $customerId = (int) $order->getCustomerId();

            $link = $this->buildPaymentLink($orderId);
            $paymentLink->setOrderId((int)$orderId);
            $paymentLink->setCustomerId($customerId);
            $paymentLink->setLink($link);
            $paymentLink->setVindiPaymentMethod($paymentMethod);
            $paymentLink->setStatus('pending');
            $this->linkRepository->save($paymentLink);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return $link;
    }

    /**
     * Save the payment link
     *
     * @param PaymentLink $paymentLink
     * @return void
     */
    public function savePaymentLink(PaymentLink $paymentLink): void
    {
        try {
            $this->linkRepository->save($paymentLink);
        } catch (\Exception $e) {
            $this->logger->error('Error saving payment link: ' . $e->getMessage());
        }
    }

    /**
     * Update payment link.
     *
     * @param PaymentLink $paymentLink
     * @return PaymentLink
     */
    public function updatePaymentLink(PaymentLink $paymentLink)
    {
        $link = '';
        try {
            $link = $this->buildPaymentLink($paymentLink->getOrderId());
            $paymentLink->setCreatedAt($this->dateTimeFactory->create()->format('Y-m-d H:i:s'));
            $paymentLink->setLink($link);
            $paymentLink->setStatus('pending');
            $this->linkRepository->save($paymentLink);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $paymentLink;
    }

    /**
     * Update payment link status to 'pending'.
     *
     * @param PaymentLink $paymentLink
     * @return void
     */
    public function updatePaymentLinkStatusToPending(PaymentLink $paymentLink): void
    {
        try {
            $paymentLink->setStatus('pending');
            $this->linkRepository->save($paymentLink);
        } catch (\Exception $e) {
            $this->logger->error('Error updating payment link status to pending: ' . $e->getMessage());
        }
    }

    /**
     * Update payment link status to 'expired'.
     *
     * @param PaymentLink $paymentLink
     * @return void
     */
    public function updatePaymentLinkStatusToExpired(PaymentLink $paymentLink): void
    {
        try {
            $paymentLink->setStatus('expired');
            $paymentLink->setExpiredAt($this->dateTimeFactory->create()->format('Y-m-d H:i:s'));

            $this->linkRepository->save($paymentLink);
        } catch (\Exception $e) {
            $this->logger->error('Error updating payment link status to expired: ' . $e->getMessage());
        }
    }

    /**
     * @param string $linkCreatedAt
     * @return bool
     */
    public function isLinkExpired(string $linkCreatedAt): bool
    {
        $currentTimestamp = $this->dateTimeFactory->create()->getTimestamp();
        $linkTimestamp = $this->dateTimeFactory->create($linkCreatedAt)->getTimestamp();
        $hoursDifference = floor(($currentTimestamp - $linkTimestamp) / (60 * 60));
        return ($linkTimestamp > $currentTimestamp) || $hoursDifference >= self::LINK_EXPIRATION_TIME;
    }

    /**
     * Update expired payment links to set status as 'expired'.
     *
     * @return void
     */
    public function updateExpiredPaymentLinks(): void
    {
        $paymentLinks = $this->paymentLinkCollectionFactory->create();
        foreach ($paymentLinks as $paymentLink) {
            if ($this->isLinkExpired($paymentLink->getCreatedAt())) {
                $paymentLink->setStatus('expired');
                $paymentLink->setExpiredAt($this->dateTimeFactory->create()->format('Y-m-d H:i:s'));

                $this->savePaymentLink($paymentLink);
            }
        }
    }

    /**
     * @param string|int $orderId
     * @return PaymentLink|bool
     */
    public function getPaymentLinkByOrderId($orderId)
    {
        $paymentLink = $this->paymentLinkFactory->create()->load($orderId, 'order_id');
        return $paymentLink->getId() ? $paymentLink : false;
    }

    /**
     * Get payment link by customer ID.
     *
     * @param int $customerId
     * @return PaymentLink|false
     */
    public function getPaymentLinkByCustomerId($customerId)
    {
        $paymentLink = $this->paymentLinkCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->getFirstItem();

        return $paymentLink->getId() ? $paymentLink : false;
    }

    /**
     * Get the most recent pending payment link by customer ID.
     *
     * @param int $customerId
     * @return PaymentLink|false
     */
    public function getMostRecentPendingPaymentLinkByCustomerId($customerId)
    {
        $paymentLink = $this->paymentLinkCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('status', 'pending')
            ->setOrder('created_at', 'DESC')
            ->getFirstItem();

        return $paymentLink->getId() ? $paymentLink : false;
    }

    /**
     * @param string|int $orderId
     * @return string
     * @throws NoSuchEntityException
     */
    protected function buildPaymentLink($orderId): string
    {
        return $this->storeManager->getStore()->getBaseUrl() . 'vindi_vr/checkout/?hash=' .
            hash_hmac('sha256', $orderId . date("Y/m/d h:i:s"), $this->helper->getModuleGeneralConfig("api_key"));
    }
}
