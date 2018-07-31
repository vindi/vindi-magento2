<?php

namespace Vindi\Payment\Model\Payment;


use Vindi\Payment\Helper\Data;

class Profile
{
    private $api, $helperData;

    public function __construct(Api $api, Data $helperData)
    {
        $this->api = $api;
        $this->helperData = $helperData;
    }

    /**
     * @param int $customerId
     * @param Object $payment
     * @return array|bool
     */
    public function create($payment, $customerId, $paymentMethodCode)
    {

        $creditCardData = [
            'holder_name' => $payment->getCcOwner(),
            'card_expiration' => str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT)
                . '/' . $payment->getCcExpYear(),
            'card_number' => $payment->getCcNumber(),
            'card_cvv' => $payment->getCcCid() ?: '000',
            'customer_id' => $customerId,
            'payment_company_code' => PaymentMethod::$cCBrands[$payment->getCcType()],
            'payment_method_code' => $paymentMethodCode
        ];

        $paymentProfile = $this->createPaymentProfile($creditCardData);

        if ($paymentProfile === false) {
            throw new \Exception('Erro ao informar os dados de cartão de crédito. Verifique os dados e tente novamente!');
        }

        $verifyMethod = $this->helperData->getShouldVerifyProfile();

        if ($verifyMethod && !$this->verifyPaymentProfile($paymentProfile['payment_profile']['id'])) {
            throw new \Exception('Não foi possível realizar a verificação do seu cartão de crédito!');
        }
        return $paymentProfile;
    }

    /**
     * Make an API request to create a Payment Profile to a Customer.
     *
     * @param $body (holder_name, card_expiration, card_number, card_cvv, customer_id)
     *
     * @return array|bool|mixed
     */
    private function createPaymentProfile($body)
    {
        // Protect credit card number.
        $dataToLog = $body;
        $dataToLog['card_number'] = '**** *' . substr($dataToLog['card_number'], -3);
        $dataToLog['card_cvv'] = '***';

        $customerId = $body['customer_id'];

        return $this->api->request('payment_profiles', 'POST', $body, $dataToLog);
    }

    /**
     * @param int $paymentProfileId
     *
     * @return array|bool
     */
    public function verifyPaymentProfile($paymentProfileId)
    {
        $verify_status = $this->api->request('payment_profiles/' . $paymentProfileId . '/verify', 'POST');
        return ($verify_status['transaction']['status'] === 'success');
    }
}