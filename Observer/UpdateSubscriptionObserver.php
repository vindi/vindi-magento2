<?php

namespace Vindi\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\CollectionFactory as VindiSubscriptionItemCollectionFactory;
use Vindi\Payment\Model\Vindi\Subscription as VindiSubscription;
use Vindi\Payment\Model\VindiSubscriptionItemFactory;
use Magento\Framework\App\ResourceConnection;

class UpdateSubscriptionObserver implements ObserverInterface
{
    /** @var ManagerInterface */
    private $messageManager;

    /** @var VindiSubscriptionItemCollectionFactory */
    private $vindiSubscriptionItemCollectionFactory;

    /** @var VindiSubscription */
    private $vindiSubscription;

    /** @var VindiSubscriptionItemFactory */
    private $vindiSubscriptionItemFactory;

    /** @var ResourceConnection */
    private $resource;

    public function __construct(
        ManagerInterface $messageManager,
        VindiSubscriptionItemCollectionFactory $vindiSubscriptionItemCollectionFactory,
        VindiSubscription $vindiSubscription,
        VindiSubscriptionItemFactory $vindiSubscriptionItemFactory,
        ResourceConnection $resource
    ) {
        $this->messageManager = $messageManager;
        $this->vindiSubscriptionItemCollectionFactory = $vindiSubscriptionItemCollectionFactory;
        $this->vindiSubscription = $vindiSubscription;
        $this->vindiSubscriptionItemFactory = $vindiSubscriptionItemFactory;
        $this->resource = $resource;
    }

    public function execute(Observer $observer)
    {
        $subscriptionId = $observer->getEvent()->getData('subscription_id');

        if (!$subscriptionId) {
            $this->messageManager->addErrorMessage(__('Missing subscription ID.'));
            return;
        }

        try {
            $this->updateSubscriptionData($subscriptionId);
            $this->checkAndSaveSubscriptionItems($subscriptionId);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error updating subscription: %1', $e->getMessage()));
        }
    }

    private function updateSubscriptionData($subscriptionId)
    {
        $connection = $this->resource->getConnection();
        $tableName = $connection->getTableName('vindi_subscription');
        $subscriptionData = $this->vindiSubscription->getSubscriptionById($subscriptionId);

        if ($subscriptionData) {
            $connection->update(
                $tableName,
                ['response_data' => json_encode($subscriptionData)],
                ['id = ?' => $subscriptionId]
            );
        }
    }

    /**
     * Check if the subscription items have changed and save
     * the new data to the database
     * @param int $subscriptionId
     */
    private function checkAndSaveSubscriptionItems($subscriptionId)
    {
        $itemsCollection = $this->vindiSubscriptionItemCollectionFactory->create();
        $itemsCollection->addFieldToFilter('subscription_id', $subscriptionId);

        $existingItems = [];
        foreach ($itemsCollection as $item) {
            $existingItems[$item->getProductItemId()] = $item;
        }

        $subscriptionData = $this->vindiSubscription->getSubscriptionById($subscriptionId);

        if (isset($subscriptionData['product_items'])) {
            $apiItems  = $subscriptionData['product_items'];
            $apiItemIds = array_column($apiItems, 'id');

            foreach ($existingItems as $itemId => $existingItem) {
                if (!in_array($itemId, $apiItemIds)) {
                    $existingItem->delete();
                }
            }

            foreach ($apiItems as $apiItem) {
                $itemId = $apiItem['id'];

                if (isset($existingItems[$itemId])) {
                    $existingItem = $existingItems[$itemId];
                    $this->updateItemIfChanged($existingItem, $apiItem);
                } else {
                    $this->createSubscriptionItem($subscriptionId, $apiItem);
                }
            }
        }
    }

    /**
     * Update the subscription item if any field has changed
     * @param \Vindi\Payment\Model\VindiSubscriptionItem $existingItem
     * @param array $apiItem
     */
    private function updateItemIfChanged($existingItem, $apiItem)
    {
        $updated = false;

        $fieldsToCheck = [
            'product_name' => $apiItem['product']['name'],
            'product_code' => $apiItem['product']['code'],
            'status' => $apiItem['status'],
            'quantity' => $apiItem['quantity'],
            'uses' => $apiItem['uses'],
            'cycles' => $apiItem['cycles'],
            'price' => $apiItem['pricing_schema']['price'],
            'pricing_schema_id' => $apiItem['pricing_schema']['id'],
            'pricing_schema_type' => $apiItem['pricing_schema']['schema_type'],
            'pricing_schema_format' => $apiItem['pricing_schema']['schema_format'] ?? 'N/A',
            'magento_product_sku' => $apiItem['product']['code']
        ];

        foreach ($fieldsToCheck as $field => $value) {
            if ($existingItem->getData($field) != $value) {
                $existingItem->setData($field, $value);
                $updated = true;
            }
        }

        if ($updated) {
            $existingItem->save();
        }
    }

    /**
     * Create a new subscription item
     * @param int $subscriptionId
     * @param array $apiItem
     */
    private function createSubscriptionItem($subscriptionId, $apiItem)
    {
        $subscriptionItem = $this->vindiSubscriptionItemFactory->create();
        $subscriptionItem->setSubscriptionId($subscriptionId);
        $subscriptionItem->setProductItemId($apiItem['id']);
        $subscriptionItem->setProductName($apiItem['product']['name']);
        $subscriptionItem->setProductCode($apiItem['product']['code']);
        $subscriptionItem->setStatus($apiItem['status']);
        $subscriptionItem->setQuantity($apiItem['quantity']);
        $subscriptionItem->setUses($apiItem['uses']);
        $subscriptionItem->setCycles($apiItem['cycles']);
        $subscriptionItem->setPrice($apiItem['pricing_schema']['price']);
        $subscriptionItem->setPricingSchemaId($apiItem['pricing_schema']['id']);
        $subscriptionItem->setPricingSchemaType($apiItem['pricing_schema']['schema_type']);
        $subscriptionItem->setPricingSchemaFormat($apiItem['pricing_schema']['schema_format'] ?? 'N/A');
        $subscriptionItem->setMagentoProductSku($apiItem['product']['code']);
        $subscriptionItem->save();
    }
}
