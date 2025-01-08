<?php

namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

/**
 * Controller for Add Discount Form
 */
class AddDiscount extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Vindi_Payment::subscription_add_discount';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Vindi_Payment::subscription');
        $resultPage->getConfig()->getTitle()->prepend(__('Add Discount'));
        return $resultPage;
    }
}
