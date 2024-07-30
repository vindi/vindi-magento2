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

class PaymentLinkService
{
    /**
     * Payment link expiration time, 24 hours
     */
    public const LINK_EXPIRATION_TIME = 24;

    /**
     * Sales email config path
     */
    public const SALES_EMAIL = 'trans_email/ident_sales/email';

    /**
     * Path to get the payment link template
     */
    public const PAYMENT_LINK_TEMPLATE_PATH = 'vindiconfiguration/general/payment_link_template';

    /**
     * @var PaymentLinkCollectionFactory
     */
    private PaymentLinkCollectionFactory $paymentLinkCollectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var PaymentLinkFactory
     */
    private PaymentLinkFactory $paymentLinkFactory;

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * @var PaymentLinkRepositoryInterface
     */
    private PaymentLinkRepositoryInterface $linkRepository;

    /**
     * @var DateTimeFactory
     */
    private DateTimeFactory $dateTimeFactory;

    /**
     * @var SendEmailService
     */
    private SendEmailService $sendEmailService;

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
    public function getPaymentLink(string|int $orderId)
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
            ->addFieldToFilter('link', ['like' => '%'.$hash.'%'])
            ->getFirstItem();
    }

    /**
     * @param string|int $orderId
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrderByOrderId(string|int $orderId)
    {
        return $this->orderRepository->get($orderId);
    }

    /**
     * @param string|int $orderId
     * @return bool
     */
    public function sendPaymentLinkEmail(string|int $orderId): bool
    {
        try {
            $paymentLink = $this->getPaymentLink($orderId);
            $order = $this->getOrderByOrderId($orderId);

            if ($paymentLink->getData()) {
                if ($this->isLinkExpired($paymentLink->getCreatedAt())) {
                    $paymentLink = $this->updatePaymentLink($paymentLink);
                }
            } else {
                $this->createPaymentLink($orderId, str_replace('vindi_payment_link_', '', $order->getPayment()->getMethod()));
                $paymentLink = $this->getPaymentLink($orderId);
            }

            $templateVars = [
                'customer_name' => $order->getCustomerFirstname(),
                'payment_link' => $paymentLink->getLink()
            ];
            $from = [
                'email' => $this->scopeConfig->getValue(self::SALES_EMAIL, ScopeInterface::SCOPE_STORE),
                'name' => $this->scopeConfig->getValue(self::SALES_EMAIL, ScopeInterface::SCOPE_STORE)
            ];

            $emailTemplateId = $this->scopeConfig->getValue(self::PAYMENT_LINK_TEMPLATE_PATH, ScopeInterface::SCOPE_STORE);
            $this->sendEmailService->sendEmailTemplate($emailTemplateId, $order->getCustomerEmail(), $order->getCustomerFirstname(), $from, $templateVars);
            return true;
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            return false;
        }
    }

    /**
     * @param string|int $orderId
     * @param string $paymentMethod
     * @return string
     */
    public function createPaymentLink(string|int $orderId, string $paymentMethod): string
    {
        $link = '';
        try {
            $paymentLink = $this->getPaymentLink($orderId);
            if (!$paymentLink->getData()) {
                $paymentLink = $this->paymentLinkFactory->create();
            }

            $link = $this->buildPaymentLink($orderId);
            $paymentLink->setOrderId((int)$orderId);
            $paymentLink->setLink($link);
            $paymentLink->setVindiPaymentMethod($paymentMethod);
            $this->linkRepository->save($paymentLink);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return $link;
    }

    /**
     * @param PaymentLink $paymentLink
     * @return string
     */
    public function updatePaymentLink(PaymentLink $paymentLink): string
    {
        $link = '';
        try {
            $link = $this->buildPaymentLink($paymentLink->getOrderId());
            $paymentLink->setCreatedAt($this->dateTimeFactory->create()->format('Y-m-d H:i:s'));
            $paymentLink->setLink($link);
            $this->linkRepository->save($paymentLink);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return $link;
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
     * @param PaymentLink $paymentLink
     * @return void
     */
    public function deletePaymentLink(PaymentLink $paymentLink): void
    {
        try {
            $this->linkRepository->delete($paymentLink);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param string|int $orderId
     * @return string
     * @throws NoSuchEntityException
     */
    protected function buildPaymentLink(string|int $orderId): string
    {
        return $this->storeManager->getStore()->getBaseUrl() . 'vindi_vr/checkout/?hash=' .
            hash_hmac('sha256', $orderId . date("Y/m/d h:i:s"), $this->helper->getModuleGeneralConfig("api_key"));
    }
}
