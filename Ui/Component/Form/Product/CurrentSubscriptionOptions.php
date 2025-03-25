<?php

namespace Vindi\Payment\Ui\Component\Form\Product;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\CollectionFactory as SubscriptionItemCollectionFactory;
use Magento\Framework\Registry;

class CurrentSubscriptionOptions implements OptionSourceInterface
{
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var SubscriptionItemCollectionFactory
     */
    protected $subscriptionItemCollectionFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param SubscriptionItemCollectionFactory $subscriptionItemCollectionFactory
     * @param Registry $registry
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        SubscriptionItemCollectionFactory $subscriptionItemCollectionFactory,
        Registry $registry
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->subscriptionItemCollectionFactory = $subscriptionItemCollectionFactory;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $productOptions = $this->getCurrentSubscriptionProductOptions();
        return $productOptions;
    }

    /**
     * Get product options for the current subscription
     *
     * @return array
     */
    protected function getCurrentSubscriptionProductOptions()
    {
        $currentSubscriptionId = $this->registry->registry('current_subscription_id');

        if (!$currentSubscriptionId) {
            return [];
        }

        $subscriptionItemCollection = $this->subscriptionItemCollectionFactory->create();
        $subscriptionItemCollection->addFieldToFilter('subscription_id', $currentSubscriptionId);

        $productOptions = [];
        foreach ($subscriptionItemCollection as $item) {
            $productOptions[] = [
                'value' => $item->getProductItemId(),
                'label' => $item->getProductName(),
            ];
        }

        return $productOptions;
    }
}
