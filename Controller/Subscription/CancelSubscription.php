<?php

namespace Vindi\Payment\Controller\Subscription;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Vindi\Payment\Model\Vindi\Subscription;

class CancelSubscription extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Subscription
     */
    protected $subscription;

    /**
     * CancelSubscription constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Subscription $subscription
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Subscription $subscription
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->subscription = $subscription;
        parent::__construct($context);
    }

    /**
     * Execute method
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $subscriptionId = $this->getRequest()->getParam('id');

        if ($subscriptionId) {
            try {
                $this->subscription->deleteAndCancelBills($subscriptionId);
                $this->messageManager->addSuccessMessage(__('Subscription canceled successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addWarningMessage(__('Something went wrong while cancel the Subscription.'));
            }
        }

        return $this->_redirect('vindi_vr/subscription/details/id/' . $subscriptionId);
    }
}
