<?php

namespace Vindi\Payment\Block;

trait InfoTrait
{
    private $cCBrands = [
        'VI' => 'Visa',
        'MC' => 'Master Card',
        'DN' => 'Diners',
        'AE' => 'American Express',
        'EL' => 'Elo',
        'HI' => 'Hiper Card',
    ];

    private $vindigStatusEnum = [
        'AuthorizedPendingCapture' => 'Authorized with success',
        'Captured' => 'Captured',
        'PartialCapture' => 'Captured',
        'NotAuthorized' => 'Captured',
        'Voided' => 'Voided',
        'PendingVoid' => 'Cancellation pending',
        'PartialVoid' => 'Partially canceled',
        'Refunded' => 'Refunded',
        'PendingRefund' => 'Pending Refund',
        'PartialRefunded' => 'Partially Refunded',
        'WithError' => 'With Error',
        'NotFoundInAcquirer' => 'Not located on the acquirer'
    ];

    public function canShowCcInfo()
    {
        return $this->getOrder()->getPayment()->getMethod() === 'vindi';
    }

    public function getCcOwner()
    {
        return $this->getOrder()->getPayment()->getCcOwner();
    }

    public function getCcInstallments()
    {
        return $this->getOrder()->getPayment()->getAdditionalInformation('cc_installments');
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
        return $this->cCBrands[$this->getOrder()->getPayment()->getCcType()];
    }
}
