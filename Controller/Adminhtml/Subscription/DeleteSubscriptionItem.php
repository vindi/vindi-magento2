<?php

namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Vindi\Payment\Model\Vindi\ProductItems;
use Vindi\Payment\Api\VindiSubscriptionItemRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\CollectionFactory as VindiSubscriptionItemCollectionFactory;
use Vindi\Payment\Api\SubscriptionRepositoryInterface;
use Vindi\Payment\Model\VindiSubscriptionItemFactory;
use Vindi\Payment\Model\Vindi\Subscription as VindiSubscription;
use Magento\Framework\App\ResourceConnection;

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

    /** @var VindiSubscriptionItemCollectionFactory */
    private $vindiSubscriptionItemCollectionFactory;

    /** @var SubscriptionRepositoryInterface */
    private $subscriptionRepository;

    /** @var VindiSubscriptionItemFactory */
    private $vindiSubscriptionItemFactory;

    /** @var VindiSubscription */
    private $vindiSubscription;

    /** @var ResourceConnection */
    private $resource;

    /**
     * @param Context $context
     * @param ProductItems $productItems
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     * @param VindiSubscriptionItemRepositoryInterface $vindiSubscriptionItemRepository
     * @param VindiSubscriptionItemCollectionFactory $vindiSubscriptionItemCollectionFactory
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param VindiSubscriptionItemFactory $vindiSubscriptionItemFactory
     * @param VindiSubscription $vindiSubscription
     * @param ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        ProductItems $productItems,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        VindiSubscriptionItemRepositoryInterface $vindiSubscriptionItemRepository,
        VindiSubscriptionItemCollectionFactory $vindiSubscriptionItemCollectionFactory,
        SubscriptionRepositoryInterface $subscriptionRepository,
        VindiSubscriptionItemFactory $vindiSubscriptionItemFactory,
        VindiSubscription $vindiSubscription,
        ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->productItems = $productItems;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->vindiSubscriptionItemRepository = $vindiSubscriptionItemRepository;
        $this->vindiSubscriptionItemCollectionFactory = $vindiSubscriptionItemCollectionFactory;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->vindiSubscriptionItemFactory = $vindiSubscriptionItemFactory;
        $this->vindiSubscription = $vindiSubscription;
        $this->resource = $resource;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $request = $this->getRequest();
        $entityId = $request->getParam('entity_id');
        $subscriptionId = null;

        if (!$entityId) {
            $this->messageManager->addErrorMessage(__('Missing required parameters.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $subscriptionItem = $this->vindiSubscriptionItemRepository->getById($entityId);
            $subscriptionId = $subscriptionItem->getSubscriptionId();
            $productItemId  = $subscriptionItem->getProductItemId();
            $productCode    = $subscriptionItem->getProductCode();

            if (!$subscriptionId) {
                throw new LocalizedException(__('Subscription ID is missing for the item.'));
            }

            if ($productCode === 'frete') {
                $this->messageManager->addErrorMessage(__("Cannot delete item with product code 'frete'."));
                return $resultRedirect->setPath('vindi_payment/subscription/edit', ['id' => $subscriptionId]);
            }

            $itemsCollection = $this->vindiSubscriptionItemCollectionFactory->create();
            $itemsCollection->addFieldToFilter('subscription_id', $subscriptionId);

            $validItems = $itemsCollection->addFieldToFilter('product_code', ['neq' => 'frete']);

            if ($validItems->getSize() <= 1) {
                $this->messageManager->addErrorMessage(__("Cannot delete item. Subscription must have at least one product item besides 'frete'."));
                return $resultRedirect->setPath('vindi_payment/subscription/edit', ['id' => $subscriptionId]);
            }

            if (!$productItemId) {
                throw new LocalizedException(__('Product item ID not found for the given subscription item.'));
            }

            $isDeleted = $this->productItems->deleteProductItem($productItemId);

            if (!$isDeleted) {
                $checkItem = $this->productItems->getProductItemById($productItemId);
                if ($checkItem) {
                    throw new LocalizedException(__('Failed to delete the item from the subscription.'));
                }
            }

            $this->vindiSubscriptionItemRepository->delete($subscriptionItem);
            $this->_eventManager->dispatch('vindi_subscription_update', ['subscription_id' => $subscriptionId]);
            $this->messageManager->addSuccessMessage(__('The item was successfully deleted from the subscription.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An unexpected error occurred while deleting the subscription item.'));
        }

        return $resultRedirect->setPath('vindi_payment/subscription/edit', ['id' => $subscriptionId]);
    }
}
