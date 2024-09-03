<?php

namespace Vindi\Payment\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Vindi\Payment\Logger\Logger;
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
     * @var Logger
     */
    private $logger;
    /**
     * @var Data
     */
    private $helperData;

    /**
     * Webhook constructor.
     * @param Api $api
     * @param Logger $logger
     * @param WebhookHandler $webhookHandler
     * @param Data $helperData
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Api $api,
        Logger $logger,
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
        parent::__construct($context);
    }

    /**
     * The route that webhooks will use.
     */
    public function execute()
    {
        if (!$this->validateRequest()) {
            $ip = $this->webhookHandler->getRemoteIp();
            $this->logger->error(__(sprintf('Invalid webhook attempt from IP %s', $ip)));
            return $this->getResponse()->setHttpResponseCode(500);
        }

        $body = file_get_contents('php://input');
        $this->logger->info("=========================");
        $this->logger->info(__(sprintf("Webhook New Event!\n%s", $body)));

        $this->webhookHandler->handle($body);

        return $this->getResponse()->setHttpResponseCode(200);
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
