<?php

namespace Vindi\Payment\Model\Payment;


class PaymentMethod
{
    const BANK_SLIP = "bank_slip";
    const CREDIT_CARD = "credit_card";
    const DEBIT_CARD = "debit_card";

    public static $cCBrands = [
        'VI' => 'visa',
        'MC' => 'mastercard',
        'DN' => 'diners_club',
        'AE' => 'american_express',
        'EL' => 'elo',
        'HI' => 'diners_club',
    ];

    public function __construct(Api $api)
    {
        $this->api = $api;
    }
    
    public function getCreditCardTypes()
    {
        $methods = $this->get();
        $types = [];

        foreach ($methods['credit_card'] as $type) {
            $types[$type['code']] = $type['name'];
        }

        return $types;
    }

    /**
     * Make an API request to retrieve Payment Methods.
     *
     * @return array|bool
     */
    public function get()
    {
        $paymentMethods = [
            'credit_card' => [],
            'debit_card' => [],
            'bank_slip' => false,
        ];

        $response = $this->api->request('payment_methods', 'GET');

        if (false === $response) {
            return $this->acceptBankSlip = false;
        }

        foreach ($response['payment_methods'] as $method) {
            if ('active' !== $method['status']) {
                continue;
            }

            if ('PaymentMethod::CreditCard' === $method['type']) {
                $paymentMethods['credit_card'] = array_merge(
                    $paymentMethods['credit_card'],
                    $method['payment_companies']
                );
            } elseif ('PaymentMethod::DebitCard' === $method['type']) {
                $paymentMethods['debit_card'] = array_merge(
                    $paymentMethods['debit_card'],
                    $method['payment_companies']
                );
            } elseif ('PaymentMethod::BankSlip' === $method['type']) {
                $paymentMethods['bank_slip'] = true;
            }
        }

        $this->acceptBankSlip = $paymentMethods['bank_slip'];

        return $paymentMethods;
    }
}