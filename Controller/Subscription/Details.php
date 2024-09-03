<?php
namespace Vindi\Payment\Controller\Subscription;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Vindi\Payment\Model\ResourceModel\Subscription\CollectionFactory as SubscriptionCollectionFactory;

class Details extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var SubscriptionCollectionFactory
     */
    protected $subscriptionCollectionFactory;

    /**
     * Details constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CustomerSession $customerSession
     * @param SubscriptionCollectionFactory $subscriptionCollectionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomerSession $customerSession,
        SubscriptionCollectionFactory $subscriptionCollectionFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute method
     *
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $subscriptionId = $this->getRequest()->getParam('id');

        if (!$this->customerSession->isLoggedIn()) {
            return $this->_redirect('customer/account');
        }

        if (!$subscriptionId) {
            return $this->_redirect('vindi/subscription/index');
        }

        /** @var \Vindi\Payment\Model\ResourceModel\Subscription\Collection $subscriptionCollection */
        $subscriptionCollection = $this->subscriptionCollectionFactory->create();
        $subscriptionCollection->addFieldToFilter('customer_id', $this->customerSession->getCustomerId());
        $subscriptionCollection->addFieldToFilter('id', $subscriptionId);

        if (!$subscriptionCollection->getSize()) {
            return $this->_redirect('vindi/subscription/index');
        }

        return $this->resultPageFactory->create();
    }
}
