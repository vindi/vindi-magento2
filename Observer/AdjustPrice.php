<?php
namespace Vindi\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AdjustPrice
 * @package Vindi\Payment\Observer
 */
class AdjustPrice implements ObserverInterface
{
    /**
     * Run observer method to adjust the price of the product.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $item = $observer->getEvent()->getData('quote_item');
        if ($item->getParentItem()) {
            $item = $item->getParentItem();
        }

        $product = $item->getProduct();

        if ($product->getData('vindi_enable_recurrence') != '1') {
            return;
        }

        $additionalOptions = $product->getCustomOption('additional_options');
        if ($additionalOptions) {
            $options = json_decode($additionalOptions->getValue(), true);
            foreach ($options as $option) {
                if (isset($option['code']) && $option['code'] === 'plan_price') {
                    $price = $option['value'];
                    $item->setCustomPrice($price);
                    $item->setOriginalCustomPrice($price);
                    $item->getProduct()->setIsSuperMode(true);
                }
            }
        }
    }
}
