<?php
namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Vindi\Payment\Model\ResourceModel\Subscription as SubscriptionResource;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Edit
 * @package Vindi\Payment\Controller\Adminhtml\Subscription
 */
class Edit extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var SubscriptionResource
     */
    private $subscriptionResource;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param PageFactory $resultPageFactory
     * @param SubscriptionResource $subscriptionResource
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PageFactory $resultPageFactory,
        SubscriptionResource $subscriptionResource
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->subscriptionResource = $subscriptionResource;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');

        if ($id) {
            try {
                $subscription = $this->subscriptionResource->getById($id);

                $this->registry->register('vindi_payment_subscription_id', $id);
                $this->registry->register('current_customer_id', $subscription->getCustomerId());
                $this->registry->register('vindi_current_subscription_payment_method', $subscription->getPaymentMethod());
                $this->registry->register('vindi_current_subscription_payment_profile', $subscription->getPaymentProfile());

            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This subscription no longer exists.'));
                return $this->_redirect('*/*/');
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Subscription #%1', $id));

        return $resultPage;
    }
}
