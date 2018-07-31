<?php

namespace Vindi\Payment\Controller\Index;

use Vindi\Payment\Helper\WebhookHandler;
use Vindi\Payment\Model\Api;

class Webhook extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    private $webhookHandler;

    public function __construct(
        \Vindi\Payment\Model\Payment\Api $api,
        \Psr\Log\LoggerInterface $logger,
        WebhookHandler $webhookHandler,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    )
    {
        $this->api = $api;
        $this->logger = $logger;
        $this->_pageFactory = $pageFactory;
        $this->webhookHandler = $webhookHandler;
        return parent::__construct($context);
    }

    /**
     * The route that webhooks will use.
     */
    public function execute()
    {
        if (!$this->validateRequest()) {
            $ip = $this->webhookHandler->getRemoteIp();

            $this->logger->error(sprintf('Invalid webhook attempt from IP %s', $ip), \Zend_Log::WARN);
//            $this->norouteAction();

            return;
        }

        $body = file_get_contents('php://input');
        $this->logger->info(sprintf("Novo evento dos webhooks!\n%s", $body));

        return $this->webhookHandler->handle($body);
    }

    /**
     * Validate the webhook for security reasons.
     *
     * @return bool
     */
    private function validateRequest()
    {
        return true;

        $systemKey = Mage::helper('vindi_subscription')->getHashKey();
        $requestKey = $this->getRequest()->getParam('key');

        return $systemKey === $requestKey;
    }
}