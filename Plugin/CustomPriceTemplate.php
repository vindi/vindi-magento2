<?php
namespace Vindi\Payment\Plugin;

/**
 * Class CustomPriceTemplate
 */
class CustomPriceTemplate
{
    /**
     * @param \Magento\Framework\Pricing\Render\Amount $subject
     * @param string $template
     * @return array
     */
    public function beforeSetTemplate(\Magento\Framework\Pricing\Render\Amount $subject, $template)
    {
        $customTemplate = 'Vindi_Payment::product/price/amount/default.phtml';

        if ($template == 'Magento_Catalog::product/price/amount/default.phtml') {
            return [$customTemplate];
        }

        return [$template];
    }
}
