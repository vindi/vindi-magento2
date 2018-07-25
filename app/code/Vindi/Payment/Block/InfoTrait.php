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
        return $this->getOrder()->getPayment()->getMethod() === 'vindi_creditcard';
    }

    public function getCcOwner($totalQtyCard = 1, $cardPosition = 1)
    {
        if ($totalQtyCard == 1) {
            return $this->getOrder()->getPayment()->getCcOwner();
        }

        return $this->getOrder()->getPayment()->getAdditionalInformation('cc_owner_' . $totalQtyCard . '_' . $cardPosition);
    }

    public function getCcInstallments($totalQtyCard = 1, $cardPosition = 1)
    {
        if ($totalQtyCard == 1) {
            return $this->getOrder()->getPayment()->getAdditionalInformation('cc_installments');
        }

        return $this->getOrder()->getPayment()->getAdditionalInformation('cc_installments_' . $totalQtyCard . '_' . $cardPosition);
    }

    public function getOrderKey()
    {
        $mundipaggResponse = json_decode($this->getOrder()->getPayment()->getAdditionalInformation('mundipagg_response'));
        return $mundipaggResponse->OrderResult->OrderKey;
    }

    public function getCcNumber($cardPosition)
    {
        $mundipaggResponse = json_decode($this->getOrder()->getPayment()->getAdditionalInformation('mundipagg_response'));
        $transactionResultCollection = $mundipaggResponse->CreditCardTransactionResultCollection[$cardPosition - 1];
        return $transactionResultCollection->CreditCard->MaskedCreditCardNumber;
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

    public function getCcBrand($totalQtyCard = 1, $cardPosition = 1)
    {
        if ($totalQtyCard == 1) {
            return $this->cCBrands[$this->getOrder()->getPayment()->getCcType()];
        }

        $ccBrand = $this->getOrder()->getPayment()->getAdditionalInformation('cc_type_' . $totalQtyCard . '_' . $cardPosition);
        return $this->cCBrands[$ccBrand];
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
