<?php

namespace Vindi\Payment\Model\Payment;

use Exception;
use Vindi\Payment\Helper\Api;

class PaymentMethod
{
    public const BANK_SLIP = 'bank_slip';

    public const BANK_SLIP_PIX = 'pix_bank_slip';

    public const PIX = 'pix';
    public const CREDIT_CARD = 'credit_card';
    public const DEBIT_CARD = 'debit_card';

    /**
     * @var \Vindi\Payment\Helper\Api
     */
    protected $api;

    protected $methods = [];

    protected $methodsCodes = [
        'mastercard' => 'MC',
        'visa' => 'VI',
        'american_express' => 'AE'
    ];

    /**
     * @param Api $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    /**
     * @return array
     */
    public function getCreditCardCodes(): array
    {
        $methods = $this->get();
        $types = [];

        if (!empty($methods)) {
            foreach ($methods['credit_card'] as $type) {
                if (isset($this->methodsCodes[$type['code']])) {
                    $types[$this->methodsCodes[$type['code']]] = $type['name'];
                }
            }
        }

        return $types;
    }

    /**
     * @return string
     */
    public function getCreditCardApiCode(string $ccType): string
    {
        $methods = $this->get();
        if ($methods) {
            foreach ($methods['credit_card'] as $type) {
                if (isset($this->methodsCodes[$type['code']]) && $ccType == $this->methodsCodes[$type['code']]) {
                    return $type['code'];
                }
            }
        }

        return $ccType;
    }

    /**
     * @return array
     */
    public function getCreditCardTypes(): array
    {
        $methods = $this->get();
        $types = [];

        if ($methods) {
            foreach ($methods['credit_card'] as $type) {
                $types[$type['code']] = $type['name'];
            }
        }

        return $types;
    }

    /**
     * Make an API request to retrieve Payment Methods.
     *
     * @return array
     */
    public function get(): array
    {
        if (empty($this->methods)) {
            $this->methods = [
                'credit_card' => [],
                'debit_card' => [],
                'bank_slip' => false,
            ];

            $response = $this->api->request('payment_methods', 'GET');

            if ($response && isset($response['payment_methods'])) {
                foreach ($response['payment_methods'] as $method) {
                    if ('active' !== $method['status']) {
                        continue;
                    }

                    if ('PaymentMethod::CreditCard' === $method['type']) {
                        $this->methods['credit_card'] = array_merge(
                            $this->methods['credit_card'],
                            $method['payment_companies']
                        );
                    } elseif ('PaymentMethod::DebitCard' === $method['type']) {
                        $paymentMethods['debit_card'] = array_merge(
                            $this->methods['debit_card'],
                            $method['payment_companies']
                        );
                    } elseif ('PaymentMethod::BankSlip' === $method['type']) {
                        $this->methods['bank_slip'] = true;
                    }
                }
            }
        }
        return $this->methods;
    }

    /**
     * @param $ccType
     *
     * @return bool
     * @throws Exception
     */
    public function isCcTypeValid($ccType)
    {
        $validCreditCardTypes = $this->getCreditCardCodes();
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

    /**
     * @param $ccType
     *
     * @return mixed
     * @throws Exception
     */
    private function getCcTypeFullName($ccType)
    {
        $fullNames = $this->getCreditCardCodes();

        if (isset($fullNames[$ccType])) {
            return $fullNames[$ccType];
        }

        throw new Exception(__("Could Not Find Payment Credit Card Type")->getText());
    }
}
