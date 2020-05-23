<?php

namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Vindi\Payment\Model\Subscription\SyncSubscriptionInterface;

/**
 * Class Sync
 */
class Sync extends Action
{
    /**
     * @var SyncSubscriptionInterface
     */
    private $syncSubscription;

    /**
     * Index constructor.
     * @param Context $context
     * @param SyncSubscriptionInterface $syncSubscription
     */
    public function __construct(
        Context $context,
        SyncSubscriptionInterface $syncSubscription
    ) {
        parent::__construct($context);
        $this->syncSubscription = $syncSubscription;
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        $this->syncSubscription->execute();

        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }
}
