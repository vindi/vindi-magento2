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
        $existingProductSkus = $subscriptionItemCollection->getColumnValues('magento_product_sku');

        $options = [];
        foreach ($productCollection as $product) {
            if (!in_array(Data::sanitizeItemSku($product->getSku()), $existingProductSkus)) {
                $options[] = [
                    'value' => $product->getSku(),
                    'label' => $product->getName(),
                ];
            }
        }

        return $options;
    }
}
