<?php

namespace Vindi\Payment\Controller\Subscription;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Vindi\Payment\Helper\Api;

/**
 * Class Cancel
 * @package Vindi\Payment\Controller\Subscription
 */
class Cancel extends Action
{
    /**
     * @var Api
     */
    private $api;

    /**
     * Cancel constructor.
     * @param Context $context
     * @param Api $api
     */
    public function __construct(
        Context $context,
        Api $api
    ) {
        parent::__construct($context);
        $this->api = $api;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();

        $id = (int)$this->getRequest()->getParam('id');
        $request = $this->api->request('subscriptions/'.$id,'DELETE');
        if ($request) {
            $this->messageManager->addSuccessMessage(__('You canceled the Subscription.'));
        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong while cancel the Subscription.'));
        }

        return $resultRedirect;
    }
}
