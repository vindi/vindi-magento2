<?php

namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Vindi\Payment\Model\Vindi\ProductItems;
use Vindi\Payment\Api\VindiSubscriptionItemRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class DeleteSubscriptionItem
 *
 * Controller for deleting a product item in the Vindi subscription
 */
class DeleteSubscriptionItem extends Action
{
    /** @var ProductItems */
    protected $productItems;

    /** @var RedirectFactory */
    protected $resultRedirectFactory;

    /** @var ManagerInterface */
    protected $messageManager;

    /** @var VindiSubscriptionItemRepositoryInterface */
    protected $vindiSubscriptionItemRepository;

    /**
     * DeleteSubscriptionItem constructor.
     * @param Context $context
     * @param ProductItems $productItems
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     * @param VindiSubscriptionItemRepositoryInterface $vindiSubscriptionItemRepository
     */
    public function __construct(
        Context $context,
        ProductItems $productItems,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        VindiSubscriptionItemRepositoryInterface $vindiSubscriptionItemRepository
    ) {
        parent::__construct($context);
        $this->productItems = $productItems;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->vindiSubscriptionItemRepository = $vindiSubscriptionItemRepository;
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $request = $this->getRequest();
        $entityId = $request->getParam('entity_id');
        $subscriptionId = $request->getParam('subscription_id');

        if (!$entityId || !$subscriptionId) {
            $this->messageManager->addErrorMessage(__('Missing required parameters.'));
            return $resultRedirect->setPath('*/*/editsubscriptionitem', ['entity_id' => $subscriptionId]);
        }

        try {
            $subscriptionItem = $this->vindiSubscriptionItemRepository->getById($entityId);
            $productItemId = $subscriptionItem->getProductItemId();

            if (!$productItemId) {
                throw new LocalizedException(__('Product item ID not found for the given subscription item.'));
            }

            $isDeleted = $this->productItems->deleteProductItem($productItemId);

            if ($isDeleted) {
                $this->messageManager->addSuccessMessage(__('The item was successfully deleted from the subscription.'));
            } else {
                throw new LocalizedException(__('Failed to delete the item from the subscription.'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while deleting the item from the subscription.'));
        }

        return $resultRedirect->setPath('*/*/editsubscriptionitem', ['entity_id' => $subscriptionId]);
    }
}
