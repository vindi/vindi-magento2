<?php

namespace Vindi\Payment\Model\Payment;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Vindi\Payment\Helper\Api;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Vindi\Payment\Model\ResourceModel\PaymentProfile\CollectionFactory as PaymentProfileCollectionFactory;
use Vindi\Payment\Model\ResourceModel\VindiCustomer\CollectionFactory as VindiCustomerCollectionFactory;
use Vindi\Payment\Model\VindiCustomerFactory;

class Customer
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $addressRepository;

    /** @var CustomerRepositoryInterface */
    protected $customerRepository;

    /** @var Api  */
    protected $api;

    /** @var ManagerInterface  */
    protected $messageManager;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /** @var PaymentProfileCollectionFactory */
    protected $paymentProfileCollectionFactory;

    /** @var VindiCustomerCollectionFactory */
    protected $vindiCustomerCollectionFactory;

    /** @var VindiCustomerFactory */
    protected $vindiCustomerFactory;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param Api $api
     * @param ManagerInterface $messageManager
     * @param AddressRepositoryInterface $addressRepository
     * @param StoreManagerInterface $storeManager
     * @param PaymentProfileCollectionFactory $paymentProfileCollectionFactory
     * @param VindiCustomerCollectionFactory $vindiCustomerCollectionFactory
     * @param VindiCustomerFactory $vindiCustomerFactory
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Api $api,
        ManagerInterface $messageManager,
        AddressRepositoryInterface $addressRepository,
        StoreManagerInterface $storeManager,
        PaymentProfileCollectionFactory $paymentProfileCollectionFactory,
        VindiCustomerCollectionFactory $vindiCustomerCollectionFactory,
        VindiCustomerFactory $vindiCustomerFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->api = $api;
        $this->messageManager = $messageManager;
        $this->addressRepository = $addressRepository;
        $this->storeManager = $storeManager;
        $this->paymentProfileCollectionFactory = $paymentProfileCollectionFactory;
        $this->vindiCustomerCollectionFactory = $vindiCustomerCollectionFactory;
        $this->vindiCustomerFactory = $vindiCustomerFactory;
    }

    /**
     * @param Order $order
     *
     * @return array|bool|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function findOrCreate(Order $order)
    {
        $billing = $order->getBillingAddress();
        $customer = null;
        $vindiCustomerId = null;

        if (!$order->getCustomerIsGuest()) {
            $customer = $this->customerRepository->get($billing->getEmail());
            $vindiCustomerId = $this->findVindiCustomerIdByCustomerId($customer->getId());
        }

        if ($vindiCustomerId) {
            if ($order->getPayment()->getMethod() == "vindi_pix") {
                $customerVindi = $this->getVindiCustomerData($customer->getId());

                if (is_array($customerVindi)) {
                    $additionalInfo = $order->getPayment()->getAdditionalInformation();
                    $taxVatOrder = str_replace([' ', '-', '.'], '', $additionalInfo['document'] ?? '');
                    if ($customerVindi['registry_code'] != $taxVatOrder) {
                        $updateData = [
                            'registry_code' => $taxVatOrder,
                        ];
                        $this->updateVindiCustomer($vindiCustomerId, $updateData);
                        $customer->setTaxvat($additionalInfo['document'] ?? '');
                        $this->customerRepository->save($customer);
                    }
                }
            }

            return $vindiCustomerId;
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

        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $baseUrl = preg_replace("(^https?://)", "", rtrim($baseUrl, "/"));
        $baseUrl = preg_replace('/[^a-zA-Z0-9]/', '_', $baseUrl);
        $uniqueCode = $baseUrl . '_' . $customer->getId() . '_' . time();

        $customerVindi = [
            'name'    => $billing->getFirstname() . ' ' . $billing->getLastname(),
            'email'   => $billing->getEmail(),
            'registry_code' => $this->getDocument($order),
            'code'    => $uniqueCode,
            'phones'  => $this->formatPhone($billing->getTelephone()),
            'address' => $address
        ];

        $vindiCustomerId = $this->createCustomer($customerVindi);

        if ($vindiCustomerId === false) {
            $this->messageManager->addErrorMessage(__('Failed while registering user. Check the data and try again'));
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Failed while registering user. Check the data and try again')
            );
        }

        $this->registerVindiCustomer($customer->getId(), $vindiCustomerId);

        return $vindiCustomerId;
    }

    /**
     * Find or create a customer on Vindi based on Magento customer account.
     *
     * @param CustomerInterface $customer
     * @return array|bool|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findOrCreateFromCustomerAccount(CustomerInterface $customer)
    {
        $vindiCustomerId = $this->findVindiCustomerIdByCustomerId($customer->getId());

        if ($vindiCustomerId) {
            return $vindiCustomerId;
        }

        $billingAddressId = $customer->getDefaultBilling();
        if (!$billingAddressId) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please add a billing address to your account before proceeding.')
            );
        }

        try {
            $billingAddress = $this->addressRepository->getById($billingAddressId);
        } catch (NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Billing address not set for customer.')
            );
        }

        $billingStreet = $billingAddress->getStreet();

        if (!$billingStreet) {
            $street = $billingAddress->getStreetLine(1);
            $number = $billingAddress->getStreetLine(2);
            $additionalDetails = $billingAddress->getStreetLine(3);
            $neighborhood = $billingAddress->getStreetLine(4);
        }

        $street = $billingStreet[0] ?? '';
        $number = $billingStreet[1] ?? '';
        $additionalDetails = $billingStreet[2] ?? '';
        $neighborhood = $billingStreet[3] ?? '';

        $region = $billingAddress->getRegion();

        $state = null;
        if ($region !== null) {
            $state = $region->getRegionCode();
        }

        if (!$state) {
            $state = $billingAddress->getRegionCode();
        }

        $address = [
            'street' => $street,
            'number' => $number,
            'additional_details' => $additionalDetails,
            'neighborhood' => $neighborhood,
            'zipcode' => $billingAddress->getPostcode(),
            'city' => $billingAddress->getCity(),
            'state' => $state,
            'country' => $billingAddress->getCountryId(),
        ];

        $registryCode = $customer->getTaxvat();
        if (empty($registryCode)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The registry code (CPF/CNPJ) is required for creating a customer on Vindi.')
            );
        }

        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $baseUrl = preg_replace("(^https?://)", "", rtrim($baseUrl, "/"));
        $baseUrl = preg_replace('/[^a-zA-Z0-9]/', '_', $baseUrl);
        $uniqueCode = $baseUrl . '_' . $customer->getId() . '_' . time();

        $customerVindi = [
            'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
            'email' => $customer->getEmail(),
            'registry_code' => $registryCode,
            'code' => $uniqueCode,
            'phones' => $this->formatPhone($billingAddress->getTelephone()),
            'address' => $address
        ];

        $vindiCustomerId = $this->createCustomer($customerVindi);

        if ($vindiCustomerId === false) {
            $this->messageManager->addErrorMessage(__('Failed while registering user. Check the data and try again'));
            return false;
        }

        $this->registerVindiCustomer($customer->getId(), $vindiCustomerId);

        return $vindiCustomerId;
    }

    /**
     * Register Vindi customer ID in vindi_customers table.
     *
     * @param int $magentoCustomerId
     * @param string $vindiCustomerId
     */
    protected function registerVindiCustomer($magentoCustomerId, $vindiCustomerId)
    {
        $vindiCustomer = $this->vindiCustomerFactory->create();
        $vindiCustomer->setMagentoCustomerId($magentoCustomerId);
        $vindiCustomer->setVindiCustomerId($vindiCustomerId);
        $vindiCustomer->save();
    }

    /**
     * Find Vindi customer ID by Magento customer ID using ORM.
     *
     * @param int $customerId
     * @return string|false
     */
    public function findVindiCustomerIdByCustomerId($customerId)
    {
        $collection = $this->vindiCustomerCollectionFactory->create();
        $item = $collection->addFieldToFilter('magento_customer_id', $customerId)->getFirstItem();
        if ($item->getId()) {
            return $item->getVindiCustomerId();
        }

        $collection = $this->paymentProfileCollectionFactory->create();
        $item = $collection->addFieldToFilter('customer_id', $customerId)->getFirstItem();
        return $item->getVindiCustomerId() ?: false;
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
     * Update customer Vindi.
     *
     * @param string $customerId
     * @param array $body
     * @return array|bool|mixed
     */
    public function updateVindiCustomer($customerId, $body)
    {
        $response = $this->api->request("customers/{$customerId}", 'PUT', $body);

        if (isset($response['customer']['id'])) {
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

    /**
     * Make an API request to retrieve an existing Customer by Email.
     *
     * @param string $query
     *
     * @return array|bool|mixed
     */
    public function findVindiCustomerByEmail($query)
    {
        $response = $this->api->request("customers?query=email={$query}", 'GET');

        if ($response && isset($response['customers']) && count($response['customers']) > 0) {
            $customers = $response['customers'];
            $activeCustomer = null;
            $inactiveCustomer = null;

            foreach ($customers as $customer) {
                if ($customer['status'] == 'active') {
                    $activeCustomer = $customer;
                    break;
                } elseif ($customer['status'] == 'inactive') {
                    $inactiveCustomer = $customer;
                }
            }

            if ($activeCustomer) {
                return $activeCustomer['id'];
            } elseif ($inactiveCustomer) {
                return $inactiveCustomer['id'];
            } else {
                return $customers[0]['id'];
            }
        }

        return false;
    }

    /**
     * Make an API request to retrieve an existing Customer Data.
     *
     * @param string $query
     *
     * @return array|bool|mixed
     */
    public function getVindiCustomerData($query)
    {
        $response = $this->api->request("customers?query=code={$query}", 'GET');

        if ($response && (1 === count($response['customers'])) && isset($response['customers'][0]['id'])) {
            return $response['customers'][0];
        }

        return false;
    }

    /**
     * @param $phone
     *
     * @return string|null
     */
    public function formatPhone($phone)
    {
        $digits = strlen('55' . preg_replace('/^0|\D+/', '', $phone));
        $phone_types = [
            12 => 'landline',
            13 => 'mobile',
        ];

        return array_key_exists($digits, $phone_types) ? $phone_types[$digits] : null;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return mixed|string
     */
    protected function getDocument(Order $order)
    {
        $document = (string) $order->getPayment()->getAdditionalInformation('document');
        if (!$document) {
            $document = (string) $order->getData('customer_taxvat');
        }
        return $document;
    }
}
