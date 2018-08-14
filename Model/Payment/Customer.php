<?php

namespace Vindi\Payment\Model\Payment;


class Customer
{
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        Api $api,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->customerRepository = $customerRepository;
        $this->api = $api;
        $this->messageManager = $messageManager;
    }

    public function findOrCreate($order)
    {
        $billing = $order->getBillingAddress();
        $customer = $this->customerRepository->get($billing->getEmail());
        $customerId = $this->findCustomerByCode($customer->getId());

        if ($customerId) {
            return $customerId;
        }

        $address = [
            'street' => $billing->getStreetLine(0),
            'number' => $billing->getStreetLine(1),
            'additional_details' => $billing->getStreetLine(2),
            'neighborhood' => $billing->getStreetLine(3),
            'zipcode' => $billing->getPostcode(),
            'city' => $billing->getCity(),
            'state' => $billing->getRegionCode(),
            'country' => $billing->getCountryId(),
        ];

        $customerVindi = [
            'name' => $billing->getFirstname() . ' ' . $billing->getLastname(),
            'email' => $billing->getEmail(),
            'registry_code' => $order->getData('customer_taxvat'),
            'code' => $customer->getId(),
            'phones' => $this->format_phone($billing->getTelephone()),
            'address' => $address
        ];

        $customerId = $this->createCustomer($customerVindi);

        if ($customerId === false) {
            $this->messageManager->addErrorMessage(__('Fail while registering the user. Verify data and try again'));
            throw new \Exception(__('Fail while registering the user. Verify data and try again')->getText());
        }

        return $customerId;
    }

    /**
     * Make an API request to create a Customer.
     *
     * @param array $body (name, email, code)
     *
     * @return array|bool|mixed
     */
    public function createCustomer($body)
    {
        if ($response = $this->api->request('customers', 'POST', $body)) {
            return $response['customer']['id'];
        }

        return false;
    }

    /**
     * Make an API request to retrieve an existing Customer.
     *
     * @param string $code
     *
     * @return array|bool|mixed
     */
    public function findCustomerByCode($code)
    {
        $response = $this->api->request("customers/search?code={$code}", 'GET');

        if ($response && (1 === count($response['customers'])) && isset($response['customers'][0]['id'])) {
            return $response['customers'][0]['id'];
        }

        return false;
    }

    public function format_phone($phone)
    {
        $digits = strlen('55' . preg_replace('/^0|\D+/', '', $phone));
        $phone_types = [
            12 => 'landline',
            13 => 'mobile',
        ];

        return array_key_exists($digits, $phone_types) ? $phone_types[$digits] : null;
    }
}