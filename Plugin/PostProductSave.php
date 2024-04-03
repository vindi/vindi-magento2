<?php
namespace Vindi\Payment\Plugin;

use Magento\Catalog\Model\ProductRepository;

/**
 * Class PostProductSave
 * @package Vindi\Payment\Plugin
 */
class PostProductSave
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * PostProductSave constructor.
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * After Save Plugin
     *
     * @param \Magento\Catalog\Model\Product $subject
     * @param \Magento\Catalog\Model\Product $result
     * @return \Magento\Catalog\Model\Product
     */
    public function afterSave(
        \Magento\Catalog\Model\Product $subject,
        \Magento\Catalog\Model\Product $result
    ) {
        if ($result->getData('vindi_enable_recurrence') === null) {
            return $result;
        }

        if ($result->getData('vindi_enable_recurrence') !== '1') {
            return $result;
        }

        $value = $this->determineValue($result);
        $result->setData('vindi_enable_recurrence', $value);

        $result->getResource()->saveAttribute($result, 'vindi_enable_recurrence');

        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     */
    private function determineValue(\Magento\Catalog\Model\Product $product)
    {
        if (!in_array($product->getTypeId(), ['simple', 'configurable', 'virtual'])) {
            return 0;
        } elseif ($product->getTypeId() === 'configurable' && $this->hasPriceVariation($product)) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    private function hasPriceVariation(\Magento\Catalog\Model\Product $product)
    {
        $variations = $product->getTypeInstance()->getUsedProducts($product);
        $price = null;

        foreach ($variations as $child) {
            if ($price === null) {
                $price = $child->getFinalPrice();
            } elseif ($price != $child->getFinalPrice()) {
                return true;
            }
        }

        return false;
    }
}
