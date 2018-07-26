<?php


namespace Vindi\Payment\Model\Payment;

class Vindi extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = "vindi";
    protected $_isOffline = true;

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }
}
