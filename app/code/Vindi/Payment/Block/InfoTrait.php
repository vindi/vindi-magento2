<?php

namespace Vindi\Payment\Block;

trait InfoTrait
{
    private $cCBrands = [
        'VI' => 'VI',
        'MC' => 'MC',
        'DN' => 'DN',
        'AE' => 'AE',
        'EL' => 'EL',
        'HI' => 'HI',
    ];

    private $mundipaggStatusEnum = [
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

    public function getOrderKey()
    {
        $mundipaggResponse = json_decode($this->getOrder()->getPayment()->getAdditionalInformation('mundipagg_response'));
        return $mundipaggResponse->OrderResult->OrderKey;
    }

    public function getCcNumber()
    {
        return $this->getOrder()->getPayment()->getCcLast4();
    }

    public function getOrderReference()
    {
        $mundipaggResponse = json_decode($this->getOrder()->getPayment()->getAdditionalInformation('mundipagg_response'));
        return $mundipaggResponse->OrderResult->OrderReference;
    }

    public function getCcValue($totalQtyCard = 1, $cardPosition = 1)
    {
        return $this->_currency->currency($this->getOrder()->getPayment()->getAdditionalInformation('cc_value_' . $totalQtyCard . '_' . $cardPosition), true, false);
    }

    public function getCcBrand()
    {
        return $this->cCBrands[$this->getOrder()->getPayment()->getCcType()];
    }

    public function getCcTransactionStatus($totalQtyCard = 1, $cardPosition = 1)
    {
        $transactionStatus = __('No status yet.');
        $cCStatusEnum = $this->getOrder()->getPayment()->getAdditionalInformation('CreditCardTransactionStatusEnum');
        if ($totalQtyCard != 1) {
            $cCStatusEnum = $this->getOrder()->getPayment()->getAdditionalInformation('CreditCardTransactionStatusEnum_' . $totalQtyCard . '_' . $cardPosition);
        }
        if (!is_null($cCStatusEnum)) {
            $transactionStatus = $this->mundipaggStatusEnum[$cCStatusEnum];
        }

        return __($transactionStatus);
    }
}
