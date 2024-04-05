<?php
namespace Vindi\Payment\Plugin;

/**
 * Class DisableQtyRendering
 * @package Vindi\Payment\Plugin
 */
class DisableQtyRendering
{
    /**
     * Checks after calling ShouldRenderQuantity whether the quantity input should be rendered.
     *
     * @param \Magento\Catalog\Block\Product\View $subject
     * @param bool $result
     * @return bool
     */
    public function afterShouldRenderQuantity(\Magento\Catalog\Block\Product\View $subject, $result)
    {
        $product = $subject->getProduct();

        if ($product && $product->getData('vindi_enable_recurrence') == '1') {
            return false;
        }

        return $result;
    }
}
