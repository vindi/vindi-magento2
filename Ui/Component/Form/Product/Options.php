<?php

namespace Vindi\Payment\Ui\Component\Form\Product;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Vindi\Payment\Helper\Data;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\CollectionFactory as SubscriptionItemCollectionFactory;
use Magento\Framework\Registry;

class Options implements OptionSourceInterface
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
        $productOptions = $this->getProductOptions();
        return $productOptions;
    }

    protected function getProductOptions()
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect(['sku', 'name']);

        $subscriptionId = $this->registry->registry('vindi_payment_subscription_id');

        $subscriptionItemCollection = $this->subscriptionItemCollectionFactory->create();
        $subscriptionItemCollection->addFieldToFilter('subscription_id', $subscriptionId);
        $existingProductData = $subscriptionItemCollection->getData();

        $options = [];
        foreach ($productCollection as $product) {
            $sku = Data::sanitizeItemSku($product->getSku());
            $existingItem = $this->getSubscriptionItemBySku($existingProductData, $sku);

            if (!$existingItem || $this->isProductValidForAddition($existingItem)) {
                $options[] = [
                    'value' => $product->getSku(),
                    'label' => $product->getName(),
                ];
            }
        }

        return $options;
    }

    /**
     * Get subscription item data by product SKU.
     *
     * @param array $subscriptionItems
     * @param string $sku
     * @return array|null
     */
    protected function getSubscriptionItemBySku(array $subscriptionItems, string $sku)
    {
        foreach ($subscriptionItems as $item) {
            if (isset($item['magento_product_sku']) && $item['magento_product_sku'] === $sku) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Check if the product is valid for addition based on "uses" and "cycles".
     *
     * @param array $item
     * @return bool
     */
    protected function isProductValidForAddition(array $item)
    {
        if (is_null($item['cycles'])) {
            return false;
        }

        if (isset($item['uses'], $item['cycles'])) {
            return $item['uses'] < $item['cycles'];
        }

        return true;
    }
}
