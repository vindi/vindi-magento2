<?php

namespace Vindi\Payment\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Vindi\Payment\Helper\Api;
use Vindi\Payment\Helper\Data;
use Vindi\Payment\Helper\WebhookHandler;

/**
 * Class Webhook
 * @package Vindi\Payment\Controller\Index
 */
class Webhook extends Action
{
    protected $_pageFactory;
    private $webhookHandler;
    /**
     * @var Api
     */
    private $api;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Data
     */
    private $helperData;

    /**
     * Webhook constructor.
     * @param Api $api
     * @param LoggerInterface $logger
     * @param WebhookHandler $webhookHandler
     * @param Data $helperData
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Api $api,
        LoggerInterface $logger,
        WebhookHandler $webhookHandler,
        Data $helperData,
        Context $context,
        PageFactory $pageFactory
    ) {
        $this->api = $api;
        $this->logger = $logger;
        $this->_pageFactory = $pageFactory;
        $this->webhookHandler = $webhookHandler;
        $this->helperData = $helperData;
        return parent::__construct($context);
    }

    /**
     * The route that webhooks will use.
     */
    public function execute()
    {
        if (!$this->validateRequest()) {
            $ip = $this->webhookHandler->getRemoteIp();

            $this->logger->error(__(sprintf('Invalid webhook attempt from IP %s', $ip)));

            return;
        }

        $body = file_get_contents('php://input');
        $this->logger->info(__(sprintf("Webhook New Event!\n%s", $body)));

        $this->webhookHandler->handle($body);
    }

    /**
     * Validate the webhook for security reasons.
     *
     * @return bool
     */
    private function validateRequest()
    {
        $systemKey = $this->helperData->getWebhookKey();
        $requestKey = $this->getRequest()->getParam('key');

        return $systemKey === $requestKey;
    }
}
