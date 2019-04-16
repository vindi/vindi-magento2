<?php

namespace Vindi\Payment\Block\Onepage;

class Bankslip extends \Magento\Framework\View\Element\Template
{
    protected $checkoutSession;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
    }

    public function getOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    public function canShowBankslip()
    {
        $order = $this->getOrder();
        if ($order->getPayment()->getMethod() === 'vindi_bankslip') {
            return true;
        }

        return false;
    }

    public function getBankslipPrintUrl()
    {
        return $this->getOrder()->getPayment()->getAdditionalInformation('print_url');
    }
}
