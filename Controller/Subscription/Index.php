<?php

namespace Vindi\Payment\Controller\Subscription;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Vindi\Payment\Model\ResourceModel\Subscription\CollectionFactory;

/**
 * Class Index
 * @package Vindi\Payment\Controller\Subscription
 * @author Iago Cedran <iago@bizcommerce.com.br>
*/
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CollectionFactory
     */
    protected $subscriptionCollectionFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CollectionFactory $subscriptionCollectionFactory
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CollectionFactory $subscriptionCollectionFactory,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * Execute method.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $customerId = $this->customerSession->getCustomerId();
        if (!$customerId) {
            return $this->_redirect('customer/account/login');
        }

        $collection = $this->subscriptionCollectionFactory->create()
            ->addFieldToFilter('client', $customerId);

        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
