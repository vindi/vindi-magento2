<?php

namespace Vindi\Payment\Model\Payment;

class Customer
{
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        Api $api,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->api = $api;
        $this->messageManager = $messageManager;
    }

    public function findOrCreate($order)
    {
        $billing = $order->getBillingAddress();
        $customer = null;
        $customerId = null;

        if (!$order->getCustomerIsGuest()) {
            $customer = $this->customerRepository->get($billing->getEmail());
            $customerId = $this->findVindiCustomer($customer->getId());
        }

        $address = [
            'street' => $billing->getStreetLine(1) ?: '',
            'number' => $billing->getStreetLine(2) ?: '',
            'additional_details' => $billing->getStreetLine(3) ?: '',
            'neighborhood' => $billing->getStreetLine(4) ?: '',
            'zipcode' => $billing->getPostcode(),
            'city' => $billing->getCity(),
            'state' => $billing->getRegionCode(),
            'country' => $billing->getCountryId(),
        ];

        $customerVindi = [
            'name' => $billing->getFirstname() . ' ' . $billing->getLastname(),
            'email' => $billing->getEmail(),
            'registry_code' => $order->getData('customer_taxvat') ?: '',
            'code' => $customer ? $customer->getId() : '',
            'phones' => $this->formatPhone($billing->getTelephone()),
            'address' => $address
        ];

        if ($customerId) {
            $this->updateCustomer($customerId, $customerVindi);
            return $customerId;
        }

        $customerId = $this->createCustomer($customerVindi);

        if ($customerId === false) {
            $this->messageManager->addErrorMessage(__('Failed while registering user. Check the data and try again'));
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Failed while registering user. Check the data and try again')
            );
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
     * @param string $query
     *
     * @return array|bool|mixed
     */
    public function findVindiCustomer($query)
    {
        $response = $this->api->request("customers?query=code={$query}", 'GET');

        if ($response && (1 === count($response['customers'])) && isset($response['customers'][0]['id'])) {
            return $response['customers'][0]['id'];
        }

        return false;
    }

    public function updateCustomer($id, $body)
    {
        if ($response = $this->api->request('customers/'.$id, 'PUT', $body)) {
            return $response['customer']['id'];
        }

        return false;
    }

    public function findVindiCustomerByEmail($email)
    {
        $response = $this->api->request("customers?query=email={$email}", 'GET');

        if ($response && (1 === count($response['customers'])) && isset($response['customers'][0]['id'])) {
            return $response['customers'][0]['id'];
        }

        return false;
    }

    public function startsWith($haystack, $needle)
    {
         $length = strlen($needle);
         return (substr($haystack, 0, $length) === $needle);
    }

    public function formatPhone($phone)
    {
        $phone = preg_replace('/^0|\D+/', '', $phone);

        // remove 55 do início caso tenha o código do país
        if ($this->startsWith($phone, '55') && strlen($phone) > 11) {
            $phone = preg_replace('/^' . preg_quote('55', '/') . '/', '', $phone);
        }
        if ($this->startsWith($phone, '0') && strlen($phone) > 11) {
            $phone = preg_replace('/^' . preg_quote('0', '/') . '/', '', $phone);
        }

        $digits = strlen('55' . preg_replace('/^0|\D+/', '', $phone));
        $phone_types = [
            12 => 'landline',
            13 => 'mobile',
        ];

        return array_key_exists($digits, $phone_types) ? [[
          'phone_type' => $phone_types[$digits],
          'number' => '55' . $phone,
          'extension' => '' 
        ]] : [];
    }
}
