<?php

namespace Vindi\Payment\Model\Payment;

use Vindi\Payment\Helper\Data;

class Profile
{
    private $api;
    private $helperData;

    public function __construct(\Vindi\Payment\Helper\Api $api, Data $helperData, PaymentMethod $paymentMethod)
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
            'card_cvv' => $payment->getCcCid() ?: '',
            'customer_id' => $customerId,
            'payment_company_code' => $payment->getCcType(),
            'payment_method_code' => $paymentMethodCode
        ];

        $paymentProfile = $this->createPaymentProfile($creditCardData);

        if ($paymentProfile === false) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Error while informing credit card data. Verify data and try again'));
        }

        $verifyMethod = $this->helperData->getShouldVerifyProfile();

        if ($verifyMethod && !$this->verifyPaymentProfile($paymentProfile['payment_profile']['id'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Impossible to validate your credit card'));
        }
        return $paymentProfile;
    }

    /**
     * @param $payment
     * @param $customerId
     * @param $paymentMethodCode
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createFromCustomerAccount($payment, $customerId, $paymentMethodCode)
    {
        $payment['customer_id'] = $customerId;
        $paymentProfile = $this->createPaymentProfileFromCustomerAccount($payment);

        if ($paymentProfile === false) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Error while informing credit card data. Verify data and try again'));
        }

        $verifyMethod = $this->helperData->getShouldVerifyProfile();

        if ($verifyMethod && !$this->verifyPaymentProfile($paymentProfile['payment_profile']['id'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Impossible to validate your credit card'));
        }
        return $paymentProfile;
    }

    /**
     * @param $body
     * @return bool|mixed
     */
    private function createPaymentProfile($body)
    {
        $dataToLog = $body;
        $dataToLog['card_number'] = '**** *' . substr($dataToLog['card_number'], -3);
        $dataToLog['card_cvv'] = '***';

        return $this->api->request('payment_profiles', 'POST', $body, $dataToLog);
    }

    /**
     * @param $body
     * @return bool|mixed
     */
    private function createPaymentProfileFromCustomerAccount($body)
    {
        $dataToLog = $body;
        $dataToLog['card_number']  = '**** *' . substr($dataToLog['card_number'], -3);
        $dataToLog['card_cvv']     = '***';
        $body['allow_as_fallback'] = true;

        return $this->api->request('payment_profiles', 'POST', $body, $dataToLog);
    }

    public function verifyPaymentProfile($paymentProfileId)
    {
        $verify_status = $this->api->request('payment_profiles/' . $paymentProfileId . '/verify', 'POST');
        return ($verify_status['transaction']['status'] === 'success');
    }

    /**
     * @param $paymentProfileId
     * @param $dataToUpdate
     * @return bool|mixed
     */
    public function updatePaymentProfile($paymentProfileId, $dataToUpdate)
    {
        $body = [
            "body" => $dataToUpdate,
            "allow_as_fallback" => true
        ];

        $updateStatus = $this->api->request('payment_profiles/' . $paymentProfileId, 'PUT', $body);
        return $updateStatus;
    }

    /**
     * @param $paymentProfileId
     * @return bool|mixed
     */
    public function deletePaymentProfile($paymentProfileId)
    {
        $deleteStatus = $this->api->request('payment_profiles/' . $paymentProfileId, 'DELETE');
        return $deleteStatus;
    }
}
