<?php
namespace Vindi\Payment\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableProductTypeInstance;
use Magento\Framework\Exception\NoSuchEntityException;
use Vindi\Payment\Helper\RecurrencePrice;

/**
 * Class ProductPlugin
 * @package Vindi\Payment\Plugin
 */
class ProductPlugin
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ConfigurableProductTypeInstance
     */
    protected $configurableProductTypeInstance;

    /**
     * @var RecurrencePrice
     */
    protected $recurrencePriceHelper;

    /**
     * ProductPlugin constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ConfigurableProductTypeInstance $configurableProductTypeInstance
     * @param RecurrencePrice $recurrencePriceHelper
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ConfigurableProductTypeInstance $configurableProductTypeInstance,
        RecurrencePrice $recurrencePriceHelper
    ) {
        $this->productRepository = $productRepository;
        $this->configurableProductTypeInstance = $configurableProductTypeInstance;
        $this->recurrencePriceHelper = $recurrencePriceHelper;
    }

    /**
     * After Get Price Plugin
     *
     * @param Product $subject
     * @param float $result
     * @return float
     */
    public function afterGetPrice(Product $subject, $result)
    {
        $minPrice = $this->recurrencePriceHelper->getMinRecurrencePrice($subject);
        return $minPrice ?? $result;
    }

    /**
     * Returns the parent product if it exists.
     *
     * @param Product $product
     * @return Product|null
     */
    protected function getParentProduct(Product $product)
    {
        $parentIds = $this->configurableProductTypeInstance->getParentIdsByChild($product->getId());
        if (!empty($parentIds)) {
            $productId = array_shift($parentIds);
            try {
                return $this->productRepository->getById($productId);
            } catch (NoSuchEntityException $e) {
                return null;
            }
        }

        return null;
    }
}
