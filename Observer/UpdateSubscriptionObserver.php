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

    /**
     * @param ManagerInterface $messageManager
     * @param VindiSubscriptionItemCollectionFactory $vindiSubscriptionItemCollectionFactory
     * @param VindiSubscription $vindiSubscription
     * @param VindiSubscriptionItemFactory $vindiSubscriptionItemFactory
     * @param ResourceConnection $resource
     */
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

    /**
     * Execute the observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $subscriptionId = $observer->getEvent()->getData('subscription_id');

        if (!$subscriptionId) {
            $this->messageManager->addErrorMessage(__('Missing subscription ID.'));
            return;
        }

        try {
            $this->updateSubscriptionData($subscriptionId);
            $this->updateSubscriptionItems($subscriptionId);
            $this->checkAndSaveSubscriptionItems($subscriptionId);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error updating subscription: %1', $e->getMessage()));
        }
    }

    /**
     * Update the "response_data" field in the "vindi_subscription" table.
     *
     * @param int $subscriptionId
     * @return void
     */
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
            $subscriptionData = $this->vindiSubscription->getSubscriptionById($subscriptionId);
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
}
