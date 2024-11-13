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
     * DeleteSubscriptionItem constructor.
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
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $request  = $this->getRequest();
        $entityId = $request->getParam('entity_id');

        if (!$entityId) {
            $this->messageManager->addErrorMessage(__('Missing required parameters.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $subscriptionItem = $this->vindiSubscriptionItemRepository->getById($entityId);
            $subscriptionId = $subscriptionItem->getSubscriptionId();
            $productItemId  = $subscriptionItem->getProductItemId();

            if (!$productItemId) {
                throw new LocalizedException(__('Product item ID not found for the given subscription item.'));
            }

            $isDeleted = $this->productItems->deleteProductItem($productItemId);

            if ($isDeleted) {
                $this->updateSubscriptionData($subscriptionId);
                $this->updateSubscriptionItems($subscriptionId);
                $this->checkAndSaveSubscriptionItems($subscriptionId);
                $this->messageManager->addSuccessMessage(__('The item was successfully deleted from the subscription.'));
            } else {
                throw new LocalizedException(__('Failed to delete the item from the subscription.'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while deleting the item from the subscription.'));
        }

        return $resultRedirect->setPath('vindi_payment/subscription/edit', ['id' => $subscriptionId]);
    }

    /**
     * Update the "response_data" field in the "vindi_subscription" table.
     *
     * @param int $subscriptionId
     * @return void
     */
    private function updateSubscriptionData($subscriptionId)
    {
        try {
            $connection = $this->resource->getConnection();
            $tableName = $connection->getTableName('vindi_subscription');
            $subscriptionData = $this->fetchSubscriptionDataFromApi($subscriptionId);

            if ($subscriptionData) {
                $connection->update(
                    $tableName,
                    ['response_data' => json_encode($subscriptionData)],
                    ['id = ?' => $subscriptionId]
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while updating subscription data.'));
        }
    }

    /**
     * Update the items in the "vindi_subscription_item" table corresponding to the subscription.
     *
     * @param int $subscriptionId
     * @return void
     */
    private function updateSubscriptionItems($subscriptionId)
    {
        $itemsCollection = $this->vindiSubscriptionItemCollectionFactory->create();
        $itemsCollection->addFieldToFilter('subscription_id', $subscriptionId);

        foreach ($itemsCollection as $item) {
            $item->delete();
        }
    }

    /**
     * Check if subscription items are saved in the database and save them if not.
     *
     * @param int $subscriptionId
     * @return void
     */
    private function checkAndSaveSubscriptionItems($subscriptionId)
    {
        $itemsCollection = $this->vindiSubscriptionItemCollectionFactory->create();
        $itemsCollection->addFieldToFilter('subscription_id', $subscriptionId);

        if ($itemsCollection->getSize() == 0) {
            $subscriptionData = $this->fetchSubscriptionDataFromApi($subscriptionId);
            if (isset($subscriptionData['product_items'])) {
                foreach ($subscriptionData['product_items'] as $item) {
                    $subscriptionItem = $this->vindiSubscriptionItemFactory->create();
                    $subscriptionItem->setSubscriptionId($subscriptionId);
                    $subscriptionItem->setProductItemId($item['id']);
                    $subscriptionItem->setProductName($item['product']['name']);
                    $subscriptionItem->setProductCode($item['product']['code']);
                    $subscriptionItem->setStatus($item['status']);
                    $subscriptionItem->setQuantity($item['quantity']);
                    $subscriptionItem->setUses($item['uses']);
                    $subscriptionItem->setCycles($item['cycles']);
                    $subscriptionItem->setPrice($item['pricing_schema']['price']);
                    $subscriptionItem->setPricingSchemaId($item['pricing_schema']['id']);
                    $subscriptionItem->setPricingSchemaType($item['pricing_schema']['schema_type']);
                    $subscriptionItem->setPricingSchemaFormat($item['pricing_schema']['schema_format'] ?? 'N/A');
                    $subscriptionItem->setMagentoProductSku($item['product']['code']);
                    $subscriptionItem->save();
                }
            }
        }
    }

    /**
     * Retrieve subscription data by ID from the API
     *
     * @param int $subscriptionId
     * @return array|null
     */
    private function fetchSubscriptionDataFromApi($subscriptionId)
    {
        return $this->vindiSubscription->getSubscriptionById($subscriptionId);
    }
}
