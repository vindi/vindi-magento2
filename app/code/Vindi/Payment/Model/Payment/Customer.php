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
        $customerId = $this->api->findCustomerByCode($customer->getId());

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

        $customerId = $this->api->createCustomer($customerVindi);

        if ($customerId === false) {
            $this->messageManager->addErrorMessage(__('Fail while registering the user. Verify data and try again'));
            throw new \Exception(__('Fail while registering the user. Verify data and try again')->getText());
        }

        return $customerId;
    }

    /**
     * @param array Customer phones $phone
     * @return array
     */
    public function format_phone($phone)
    {
        $phone = '55' . preg_replace('/^0|\D+/', '', $phone);

        switch (strlen($phone)) {
            case 12:
                $phone_type = 'landline';
                break;
            case 13:
                $phone_type = 'mobile';
                break;
        }

        if (isset($phone_type)) {
            return [[
                'phone_type' => $phone_type,
                'number' => $phone
            ]];
        }
    }
}