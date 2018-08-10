<?php

namespace Vindi\Payment\Model\Payment;


use Vindi\Payment\Helper\Data;

class Profile
{
    private $api, $helperData;

    public function __construct(Api $api, Data $helperData, PaymentMethod $paymentMethod)
    {
        $this->api = $api;
        $this->helperData = $helperData;
        $this->paymentMethod = $paymentMethod;
    }

    public function create($payment, $customerId, $paymentMethodCode)
    {
        $creditCardData = [
            'holder_name' => $payment->getCcOwner(),
            'card_expiration' => str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT)
                . '/' . $payment->getCcExpYear(),
            'card_number' => $payment->getCcNumber(),
            'card_cvv' => $payment->getCcCid() ?: '000',
            'customer_id' => $customerId,
            'payment_company_code' => $payment->getCcType(),
            'payment_method_code' => $paymentMethodCode
        ];

        $paymentProfile = $this->createPaymentProfile($creditCardData);

        if ($paymentProfile === false) {
            throw new \Exception(__('Error while informing credit card data. Verify data and try again')->getText());
        }

        $verifyMethod = $this->helperData->getShouldVerifyProfile();

        if ($verifyMethod && !$this->verifyPaymentProfile($paymentProfile['payment_profile']['id'])) {
            throw new \Exception(__('Impossible to validate your credit card')->getText());
        }
        return $paymentProfile;
    }

    private function createPaymentProfile($body)
    {
        // Protect credit card number.
        $dataToLog = $body;
        $dataToLog['card_number'] = '**** *' . substr($dataToLog['card_number'], -3);
        $dataToLog['card_cvv'] = '***';

        return $this->api->request('payment_profiles', 'POST', $body, $dataToLog);
    }

    public function verifyPaymentProfile($paymentProfileId)
    {
        $verify_status = $this->api->request('payment_profiles/' . $paymentProfileId . '/verify', 'POST');
        return ($verify_status['transaction']['status'] === 'success');
    }
}