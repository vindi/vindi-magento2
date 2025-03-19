<?php

namespace Vindi\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\CollectionFactory as VindiSubscriptionItemCollectionFactory;
use Vindi\Payment\Model\Vindi\Subscription as VindiSubscription;
use Vindi\Payment\Model\VindiSubscriptionItemFactory;
use Magento\Framework\App\ResourceConnection;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItemDiscount\CollectionFactory as VindiSubscriptionItemDiscountCollectionFactory;
use Vindi\Payment\Model\VindiSubscriptionItemDiscountFactory;

/**
 * Class UpdateSubscriptionObserver
 *
 * @package Vindi\Payment\Observer
 */
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

    /** @var VindiSubscriptionItemDiscountCollectionFactory */
    private $vindiSubscriptionItemDiscountCollectionFactory;

    /** @var VindiSubscriptionItemDiscountFactory */
    private $vindiSubscriptionItemDiscountFactory;

    public function __construct(
        ManagerInterface $messageManager,
        VindiSubscriptionItemCollectionFactory $vindiSubscriptionItemCollectionFactory,
        VindiSubscription $vindiSubscription,
        VindiSubscriptionItemFactory $vindiSubscriptionItemFactory,
        ResourceConnection $resource,
        VindiSubscriptionItemDiscountCollectionFactory $vindiSubscriptionItemDiscountCollectionFactory,
        VindiSubscriptionItemDiscountFactory $vindiSubscriptionItemDiscountFactory
    ) {
        $this->messageManager = $messageManager;
        $this->vindiSubscriptionItemCollectionFactory = $vindiSubscriptionItemCollectionFactory;
        $this->vindiSubscription = $vindiSubscription;
        $this->vindiSubscriptionItemFactory = $vindiSubscriptionItemFactory;
        $this->resource = $resource;
        $this->vindiSubscriptionItemDiscountCollectionFactory = $vindiSubscriptionItemDiscountCollectionFactory;
        $this->vindiSubscriptionItemDiscountFactory = $vindiSubscriptionItemDiscountFactory;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $subscriptionId = $observer->getEvent()->getData('subscription_id');
        $skipSync = $observer->getEvent()->getData('skip_sync_discount') ?? false;

        if (!$subscriptionId) {
            $this->messageManager->addErrorMessage(__('Missing subscription ID.'));
            return;
        }

        try {
            $this->updateSubscriptionData($subscriptionId);
            $this->checkAndSaveSubscriptionItems($subscriptionId);
            if (!$skipSync) {
                $this->checkAndSyncSubscriptionDiscounts($subscriptionId);
            }
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
     *
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

        if (isset($subscriptionData['product_items']) && is_array($subscriptionData['product_items'])) {
            $apiItems  = $subscriptionData['product_items'];
            $apiItemIds = [];
            foreach ($apiItems as $apiItem) {
                $apiItemIds[] = $apiItem['id'] ?? null;
            }

            foreach ($existingItems as $itemId => $existingItem) {
                if (!in_array($itemId, $apiItemIds, true)) {
                    $existingItem->delete();
                }
            }

            foreach ($apiItems as $apiItem) {
                $itemId = $apiItem['id'] ?? null;
                if (!$itemId) {
                    continue;
                }

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
     *
     * @param \Vindi\Payment\Model\VindiSubscriptionItem $existingItem
     * @param array $apiItem
     */
    private function updateItemIfChanged($existingItem, $apiItem)
    {
        $updated = false;

        $fieldsToCheck = [
            'product_name' => $apiItem['product']['name'] ?? '',
            'product_code' => $apiItem['product']['code'] ?? '',
            'status' => $apiItem['status'] ?? '',
            'quantity' => $apiItem['quantity'] ?? 0,
            'uses' => $apiItem['uses'] ?? 0,
            'cycles' => $apiItem['cycles'] ?? 0,
            'price' => $apiItem['pricing_schema']['price'] ?? 0,
            'pricing_schema_id' => $apiItem['pricing_schema']['id'] ?? 0,
            'pricing_schema_type' => $apiItem['pricing_schema']['schema_type'] ?? '',
            'pricing_schema_format' => $apiItem['pricing_schema']['schema_format'] ?? 'N/A',
            'magento_product_sku' => $apiItem['product']['code'] ?? ''
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
     *
     * @param int $subscriptionId
     * @param array $apiItem
     */
    private function createSubscriptionItem($subscriptionId, $apiItem)
    {
        $subscriptionItem = $this->vindiSubscriptionItemFactory->create();
        $subscriptionItem->setSubscriptionId($subscriptionId);
        $subscriptionItem->setProductItemId($apiItem['id'] ?? 0);
        $subscriptionItem->setProductName($apiItem['product']['name'] ?? '');
        $subscriptionItem->setProductCode($apiItem['product']['code'] ?? '');
        $subscriptionItem->setStatus($apiItem['status'] ?? '');
        $subscriptionItem->setQuantity($apiItem['quantity'] ?? 0);
        $subscriptionItem->setUses($apiItem['uses'] ?? 0);
        $subscriptionItem->setCycles($apiItem['cycles'] ?? 0);
        $subscriptionItem->setPrice($apiItem['pricing_schema']['price'] ?? 0);
        $subscriptionItem->setPricingSchemaId($apiItem['pricing_schema']['id'] ?? 0);
        $subscriptionItem->setPricingSchemaType($apiItem['pricing_schema']['schema_type'] ?? '');
        $subscriptionItem->setPricingSchemaFormat($apiItem['pricing_schema']['schema_format'] ?? 'N/A');
        $subscriptionItem->setMagentoProductSku($apiItem['product']['code'] ?? '');
        $subscriptionItem->save();
    }

    /**
     * Check and synchronize subscription discounts with API data
     *
     * @param int $subscriptionId
     */
    private function checkAndSyncSubscriptionDiscounts($subscriptionId)
    {
        $subscriptionData = $this->vindiSubscription->getSubscriptionById($subscriptionId);
        if (!isset($subscriptionData['product_items']) || !is_array($subscriptionData['product_items'])) {
            return;
        }

        $apiDiscounts = [];
        foreach ($subscriptionData['product_items'] as $apiItem) {
            if (!isset($apiItem['id'])) {
                continue;
            }
            if (!isset($apiItem['product']) || !is_array($apiItem['product'])) {
                continue;
            }
            if (isset($apiItem['discounts']) && is_array($apiItem['discounts']) && !empty($apiItem['discounts'])) {
                foreach ($apiItem['discounts'] as $discountData) {
                    if (!isset($discountData['id'])) {
                        continue;
                    }
                    $apiDiscounts[$discountData['id']] = [
                        'vindi_discount_id' => $discountData['id'],
                        'subscription_id' => $subscriptionId,
                        'product_item_id' => $apiItem['id'] ?? 0,
                        'product_name' => $apiItem['product']['name'] ?? '',
                        'magento_product_sku' => $apiItem['product']['code'] ?? '',
                        'discount_type' => $discountData['discount_type'] ?? '',
                        'percentage' => $discountData['percentage'] ?? null,
                        'amount' => $discountData['amount'] ?? null,
                        'quantity' => $discountData['quantity'] ?? null,
                        'cycles' => $discountData['cycles'] ?? null,
                    ];
                }
            }
        }

        $discountCollection = $this->vindiSubscriptionItemDiscountCollectionFactory->create();
        $discountCollection->addFieldToFilter('subscription_id', $subscriptionId);

        $existingDiscounts = [];
        foreach ($discountCollection as $discount) {
            $existingDiscounts[$discount->getVindiDiscountId()] = $discount;
        }

        foreach ($existingDiscounts as $vindiDiscountId => $existingDiscount) {
            if (!isset($apiDiscounts[$vindiDiscountId])) {
                $existingDiscount->delete();
            }
        }

        foreach ($apiDiscounts as $vindiDiscountId => $apiDiscount) {
            if (isset($existingDiscounts[$vindiDiscountId])) {
                $this->updateDiscountIfChanged($existingDiscounts[$vindiDiscountId], $apiDiscount);
            } else {
                $this->createSubscriptionDiscount($subscriptionId, $apiDiscount);
            }
        }
    }

    /**
     * Update the subscription discount if any field has changed
     *
     * @param \Vindi\Payment\Model\VindiSubscriptionItemDiscount $existingDiscount
     * @param array $apiDiscount
     */
    private function updateDiscountIfChanged($existingDiscount, array $apiDiscount)
    {
        $updated = false;

        $fieldsToCheck = [
            'product_item_id' => $apiDiscount['product_item_id'] ?? 0,
            'product_name' => $apiDiscount['product_name'] ?? '',
            'magento_product_sku' => $apiDiscount['magento_product_sku'] ?? '',
            'discount_type' => $apiDiscount['discount_type'] ?? '',
            'percentage' => $apiDiscount['percentage'] ?? null,
            'amount' => $apiDiscount['amount'] ?? null,
            'quantity' => $apiDiscount['quantity'] ?? null,
            'cycles' => $apiDiscount['cycles'] ?? null
        ];

        foreach ($fieldsToCheck as $field => $value) {
            if ($existingDiscount->getData($field) != $value) {
                $existingDiscount->setData($field, $value);
                $updated = true;
            }
        }

        if ($updated) {
            $existingDiscount->save();
        }
    }

    /**
     * Create a new subscription discount
     *
     * @param int $subscriptionId
     * @param array $apiDiscount
     */
    private function createSubscriptionDiscount($subscriptionId, array $apiDiscount)
    {
        $discount = $this->vindiSubscriptionItemDiscountFactory->create();
        $discount->setData([
            'vindi_discount_id'   => $apiDiscount['vindi_discount_id'] ?? 0,
            'subscription_id'      => $subscriptionId,
            'product_item_id'      => $apiDiscount['product_item_id'] ?? 0,
            'product_name'         => $apiDiscount['product_name'] ?? '',
            'magento_product_sku'  => $apiDiscount['magento_product_sku'] ?? '',
            'discount_type'        => $apiDiscount['discount_type'] ?? '',
            'percentage'           => $apiDiscount['percentage'] ?? null,
            'amount'               => $apiDiscount['amount'] ?? null,
            'quantity'             => $apiDiscount['quantity'] ?? null,
            'cycles'               => $apiDiscount['cycles'] ?? null
        ]);
        $discount->save();
    }
}
