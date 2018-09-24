<?php

namespace Vindi\Payment\Block;

use Vindi\Payment\Helper\Data;

trait InfoTrait
{
    public function canShowCcInfo()
    {   
        $moduleStatus = (new Data)->getModuleGeneralConfig('module_status');
        if ($moduleStatus)
            return $this->getOrder()->getPayment()->getMethod() === 'vindi';
    }

    public function getCcOwner()
    {
        return $this->getOrder()->getPayment()->getCcOwner();
    }

    public function getCcInstallments()
    {
        return $this->getOrder()->getPayment()->getAdditionalInformation('installments');
    }

    public function getCcNumber()
    {
        return $this->getOrder()->getPayment()->getCcLast4();
    }

    public function getCcValue($totalQtyCard = 1, $cardPosition = 1)
    {
        return $this->_currency->currency($this->getOrder()->getPayment()->getAdditionalInformation('cc_value_' . $totalQtyCard . '_' . $cardPosition), true, false);
    }

    public function getCcBrand()
    {
        $brands = $this->paymentMethod->getCreditCardTypes();
        $CardCode = $this->getOrder()->getPayment()->getCcType();

        return $brands[$CardCode];
    }
}
