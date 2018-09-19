<?php

namespace Vindi\Payment\Model\Payment;

use Vindi\Payment\Helper\Data;

class PaymentMethod
{
    const BANK_SLIP = "bank_slip";
    const CREDIT_CARD = "credit_card";
    const DEBIT_CARD = "debit_card";
    private $moduleStatus;

    public function __construct(Api $api, \Magento\Payment\Model\CcConfig $ccConfig, Data $data)
    {	
	      $this->moduleStatus = $data->getModuleGeneralConfig("module_status");
        $this->api = $api;
        $this->ccConfig = $ccConfig;
    }

    public function getCreditCardTypes()
    {	
	if (!$this->moduleStatus)
	    return [];

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

    public function isCcTypeValid($ccType)
    {
        $validCreditCardTypes = $this->getCreditCardTypes();
        $fullName = $this->getCcTypeFullName($ccType);
        $fullTrimmedName = strtolower(str_replace(' ', '', $fullName));

        foreach ($validCreditCardTypes as $validCreditCardType) {
            $trimmedName = strtolower(str_replace(' ', '', $validCreditCardType));

            if ($trimmedName == $fullTrimmedName) {
                return true;
            }
        }

        return false;

    }

    private function getCcTypeFullName($ccType)
    {
        $fullNames = $this->getCreditCardTypes();

        if (isset($fullNames[$ccType])) {
            return $fullNames[$ccType];
        }

        throw new \Exception(__("Could Not Find Payment Credit Card Type")->getText());
    }
}