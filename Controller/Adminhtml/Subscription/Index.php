<?php

namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use DateTime;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Vindi\Payment\Model\Subscription\SyncSubscriptionInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Index
 * @package Vindi\Payment\Controller\Adminhtml\Subscription
 */
class Index extends Action
{
    const VINDI_SUBSCRIPTION_LAST_SYNC = 'vindi/subscription/last_sync';
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var SyncSubscriptionInterface
     */
    private $syncSubscription;
    /**
     * @var WriterInterface
     */
    private $configWriter;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param SyncSubscriptionInterface $syncSubscription
     * @param WriterInterface $configWriter
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SyncSubscriptionInterface $syncSubscription,
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
        $this->syncSubscription = $syncSubscription;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $this->syncSubscription();

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("Subscriptions"));

        return $resultPage;
    }

    /**
     * @return void
     */
    private function syncSubscription()
    {
        if ($this->availableToSync()) {
            $this->syncSubscription->execute();
            $this->updateLastSync();
        }

        return;
    }

    /**
     * @return bool
     */
    private function availableToSync()
    {
        $currentDate = new DateTime('now');
        $lastSync = $this->scopeConfig->getValue(self::VINDI_SUBSCRIPTION_LAST_SYNC);

        return $lastSync < $currentDate->format("Y-m-d");
    }

    /**
     * @return void
     */
    private function updateLastSync()
    {
        $currentDate = new DateTime('now');
        $value = $currentDate->format("Y-m-d");

        $this->configWriter->save(self::VINDI_SUBSCRIPTION_LAST_SYNC, $value);
    }
}
