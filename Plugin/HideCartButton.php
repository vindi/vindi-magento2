<?php
namespace Vindi\Payment\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;

/**
 * Class HideCartButton
 * @package Vindi\Payment\Plugin
 */
class HideCartButton
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * HideCartButton constructor.
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Changes the behavior of isSaleable based on custom logic.
     *
     * @param Product $product
     * @param bool $result
     * @return bool
     */
    public function afterIsSaleable(Product $product, $result)
    {
        if ($this->isProductPage()) {
            return $result;
        }

        if ($product->getData('vindi_enable_recurrence') == '1') {
            return false;
        }

        return $result;
    }

    /**
     * Checks whether the current page is the product detail page.
     *
     * @return bool
     */
    protected function isProductPage()
    {
        return $this->request->getFullActionName() == 'catalog_product_view';
    }
}
