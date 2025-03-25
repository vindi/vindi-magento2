<?php
namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

/**
 * Class AddDiscount
 * Handles the action for adding a discount to a subscription.
 */
class AddDiscount extends Action
{
    /**
     * Authorization level of a basic admin session.
     */
    const ADMIN_RESOURCE = 'Vindi_Payment::subscription';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $subscriptionId = (int) $this->getRequest()->getParam('id');

        if (!$subscriptionId) {
            $this->messageManager->addErrorMessage(__('Unable to find subscription to add discount.'));
            return $this->_redirect('*/*/');
        }

        $this->registry->register('current_subscription_id', $subscriptionId);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Add Discount to Subscription #%1', $subscriptionId));

        return $resultPage;
    }
}
