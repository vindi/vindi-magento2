<?php

namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Vindi\Payment\Model\Vindi\ProductItems;
use Vindi\Payment\Model\Vindi\ProductManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\CollectionFactory as VindiSubscriptionItemCollectionFactory;
use Vindi\Payment\Api\SubscriptionRepositoryInterface;
use Vindi\Payment\Model\Vindi\Subscription as VindiSubscription;
use Vindi\Payment\Model\VindiSubscriptionItemFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Class SaveAddNewItem
 *
 * @package Vindi\Payment\Controller\Adminhtml\Subscription
 */
class SaveAddNewItem extends Action
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var ProductManagement */
    protected $productManagement;

    /** @var ProductItems */
    protected $productItems;

    /** @var JsonFactory */
    protected $resultJsonFactory;

    /** @var RedirectFactory */
    protected $resultRedirectFactory;

    /** @var ManagerInterface */
    protected $messageManager;

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
     * SaveAddNewItem constructor.
     *
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param ProductManagement $productManagement
     * @param ProductItems $productItems
     * @param JsonFactory $resultJsonFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     * @param VindiSubscriptionItemCollectionFactory $vindiSubscriptionItemCollectionFactory
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     * @param VindiSubscriptionItemFactory $vindiSubscriptionItemFactory
     * @param VindiSubscription $vindiSubscription
     * @param ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        ProductManagement $productManagement,
        ProductItems $productItems,
        JsonFactory $resultJsonFactory,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        VindiSubscriptionItemCollectionFactory $vindiSubscriptionItemCollectionFactory,
        SubscriptionRepositoryInterface $subscriptionRepository,
        VindiSubscriptionItemFactory $vindiSubscriptionItemFactory,
        VindiSubscription $vindiSubscription,
        ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->productManagement = $productManagement;
        $this->productItems = $productItems;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->vindiSubscriptionItemCollectionFactory = $vindiSubscriptionItemCollectionFactory;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->vindiSubscriptionItemFactory = $vindiSubscriptionItemFactory;
        $this->vindiSubscription = $vindiSubscription;
        $this->resource = $resource;
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultJson = $this->resultJsonFactory->create();
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $resultJson->setData(['error' => true, 'message' => __('Invalid request method.')]);
        }

        try {
            $subscriptionId = $request->getPostValue('id');
            $postData = $request->getPostValue('settings');
            $productSku = $postData['data']['sku'] ?? null;
            $quantity = $postData['quantity'] ?? null;
            $status = $postData['status'] ?? null;
            $cycles = $postData['data']['cycles'] ?? null;
            $price = $postData['price'] ?? null;

            if (!$productSku || !$quantity || !$subscriptionId || $status === null || !$cycles || $price === null) {
                throw new LocalizedException(__('Missing required data: product_sku, quantity, subscription_id, status, cycles, or price.'));
            }

            $product = $this->productRepository->get($productSku);
            $vindiProductId = $this->productManagement->findOrCreate($product);
            $statusValue = $status ? 'active' : 'inactive';

            $data = [
                'product_id' => $vindiProductId,
                'subscription_id' => $subscriptionId,
                'quantity' => $quantity,
                'status' => $statusValue,
                'cycles' => $cycles,
                'pricing_schema' => [
                    'price' => $price
                ]
            ];

            $response = $this->productItems->createProductItem($data);

            if (!$response) {
                throw new LocalizedException(__('Failed to create new item in Vindi subscription.'));
            }

            $this->updateSubscriptionData($subscriptionId);
            $this->updateSubscriptionItems($subscriptionId);
            $this->checkAndSaveSubscriptionItems($subscriptionId);

            $this->messageManager->addSuccessMessage(__('New item added to subscription successfully.'));

            return $resultRedirect->setPath('vindi_payment/subscription/edit', ['id' => $subscriptionId]);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while adding the item to the subscription.'));
        }

        return $resultRedirect->setPath('vindi_payment/subscription/edit', ['id' => $this->getRequest()->getParam('subscription_id')]);
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
