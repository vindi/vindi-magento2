<?php
namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Vindi\Payment\Model\VindiSubscriptionItemFactory;

/**
 * SubscriptionItem Edit Controller
 */
class Editsubscriptionitem extends Action
{
    protected $resultPageFactory;
    protected $registry;
    protected $subscriptionItemFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        VindiSubscriptionItemFactory $subscriptionItemFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->subscriptionItemFactory = $subscriptionItemFactory;
        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $entityId = $this->getRequest()->getParam('entity_id');
        $model = $this->subscriptionItemFactory->create();

        if ($entityId) {
            $model->load($entityId);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This subscription item no longer exists.'));
                return $this->_redirect('*/*/');
            }
        }

        $this->registry->register('subscription_item', $model);

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Subscription #%1 > Item #%2', $model->getSubscriptionId(), $model->getProductItemId()));

        return $resultPage;
    }
}
