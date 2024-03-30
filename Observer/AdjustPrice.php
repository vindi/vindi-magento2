<?php
namespace Vindi\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class AdjustPrice
 * @package Vindi\Payment\Observer
 */
class AdjustPrice implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getEvent()->getData('quote_item');
        $additionalOptions = $item->getProduct()->getCustomOption('additional_options');

        if ($additionalOptions) {
            $options = json_decode($additionalOptions->getValue(), true);
            foreach ($options as $option) {
                if ($option['label'] == 'Selected Plan Price') {
                    $price = $option['value'];
                    $item->setCustomPrice($price);
                    $item->setOriginalCustomPrice($price);
                    $item->getProduct()->setIsSuperMode(true);
                }
            }
        }
    }
}
