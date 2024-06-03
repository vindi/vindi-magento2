<?php

namespace Vindi\Payment\Controller\PaymentProfile;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;
use Vindi\Payment\Model\Payment\Profile as PaymentProfileManager;
use Vindi\Payment\Model\PaymentProfileFactory;
use Vindi\Payment\Model\PaymentProfileRepository;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Vindi\Payment\Model\Payment\Customer as VindiCustomer;
use Vindi\Payment\Model\SubscriptionFactory;
use Vindi\Payment\Model\ResourceModel\Subscription as SubscriptionResource;
use Vindi\Payment\Helper\Api;

/**
 * Class Save
 * @package Vindi\Payment\Controller\PaymentProfile
 */
class Save extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var PaymentProfileManager
     */
    protected $paymentProfileManager;

    /**
     * @var PaymentProfileFactory
     */
    protected $paymentProfileFactory;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var PaymentProfileRepository
     */
    protected $paymentProfileRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var VindiCustomer
     */
    protected $vindiCustomer;

    /**
     * @var SubscriptionFactory
     */
    protected $subscriptionFactory;

    /**
     * @var SubscriptionResource
     */
    protected $subscriptionResource;

    /**
     * @var Api
     */
    protected $api;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param PaymentProfileFactory $paymentProfileFactory
     * @param PaymentProfileRepository $paymentProfileRepository
     * @param PaymentProfileManager $paymentProfileManager
     * @param DataPersistorInterface $dataPersistor
     * @param CustomerRepositoryInterface $customerRepository
     * @param VindiCustomer $vindiCustomer
     * @param SubscriptionFactory $subscriptionFactory
     * @param SubscriptionResource $subscriptionResource
     * @param Api $api
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        PaymentProfileFactory $paymentProfileFactory,
        PaymentProfileRepository $paymentProfileRepository,
        PaymentProfileManager $paymentProfileManager,
        DataPersistorInterface $dataPersistor,
        CustomerRepositoryInterface $customerRepository,
        VindiCustomer $vindiCustomer,
        SubscriptionFactory $subscriptionFactory,
        SubscriptionResource $subscriptionResource,
        Api $api
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->paymentProfileFactory = $paymentProfileFactory;
        $this->paymentProfileRepository = $paymentProfileRepository;
        $this->paymentProfileManager = $paymentProfileManager;
        $this->dataPersistor = $dataPersistor;
        $this->customerRepository = $customerRepository;
        $this->vindiCustomer = $vindiCustomer;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionResource = $subscriptionResource;
        $this->api = $api;
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    /**
     * Execute action
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        $request    = $this->getRequest();
        $data       = $request->getPostValue();
        $customerId = $this->customerSession->getCustomerId();

        try {
            $entityId       = isset($data['entity_id']) ? (int) $data['entity_id'] : null;
            $subscriptionId = isset($data['subscription_id']) ? (int) $data['subscription_id'] : null;
            $paymentProfile = $this->paymentProfileFactory->create();

            if ($entityId) {
                $paymentProfile = $this->paymentProfileRepository->getById($entityId);
            }

            $vindiData = $this->formatPaymentProfileData($data, $customerId);

            $customer = $this->customerRepository->getById($customerId);
            $customerVindiId = $this->vindiCustomer->findOrCreateFromCustomerAccount($customer);

            $vindiPaymentProfile = $this->paymentProfileManager->createFromCustomerAccount($vindiData, $customerVindiId, 'credit_card');

            $this->setCreditCardData($data);

            $paymentProfile->setData([
                'payment_profile_id' => $vindiPaymentProfile['payment_profile']['id'],
                'vindi_customer_id'  => $customerVindiId,
                'customer_id'        => $customerId,
                'customer_email'     => $customer->getEmail(),
                'cc_number'          => $data['cc_number'],
                'cc_exp_date'        => $data['cc_exp_date'],
                'cc_name'            => $data['cc_name'],
                'cc_type'            => $data['cc_type'],
                'cc_last_4'          => $data['cc_last_4'],
                'status'             => $vindiPaymentProfile["payment_profile"]["status"],
                'token'              => $vindiPaymentProfile["payment_profile"]["token"],
                'type'               => $vindiPaymentProfile["payment_profile"]["type"]
            ]);

            $this->paymentProfileRepository->save($paymentProfile);

            if ($subscriptionId) {
                if ($this->updateVindiPaymentProfile($vindiPaymentProfile['payment_profile']['id'], $subscriptionId)) {
                    $this->updateSubscriptionPaymentProfile($subscriptionId, $vindiPaymentProfile['payment_profile']['id']);
                }
            }

            $this->messageManager->addSuccessMessage(__('New payment profile created successfully.'));
            $this->dataPersistor->set('vindi_payment_profile', $data);
        } catch (\Exception $e) {
            $this->messageManager->addWarningMessage(__('An error occurred while saving the payment profile: ') . '"' . $e->getMessage() . '"');
            $this->dataPersistor->set('vindi_payment_profile', $data);
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/edit', ['id' => $entityId]);
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('vindi_vr/paymentprofile/index');
    }

    /**
     * Format payment profile data
     *
     * @param array $data
     * @param int $customerId
     * @return array
     */
    private function formatPaymentProfileData($data, $customerId)
    {
        $cardNumber = preg_replace('/\D/', '', $data['cc_number']);

        $expirationParts = explode('/', $data['cc_exp_date']);
        $expirationYear  = (strlen($expirationParts[1]) == 2) ? '20' . $expirationParts[1] : $expirationParts[1];
        $cardExpiration  = $expirationParts[0] . '/' . $expirationYear;
        $paymentCompanyCode = $data['cc_type'];

        $formattedData = [
            'holder_name'          => $data['cc_name'],
            'card_expiration'      => $cardExpiration,
            'card_number'          => $cardNumber,
            'card_cvv'             => $data['cc_cvv'],
            'customer_id'          => $customerId,
            'payment_company_code' => $paymentCompanyCode,
            'payment_method_code'  => 'credit_card',
        ];

        return $formattedData;
    }

    /**
     * Mask credit card number
     *
     * @param $cardNumber
     * @return string
     */
    private function maskCreditCardNumber($cardNumber)
    {
        $lastFourDigits = substr($cardNumber, -4);
        $maskLength     = strlen($cardNumber) - 4;
        $mask           = str_repeat("*", $maskLength);

        $maskedCardNumber = $mask . $lastFourDigits;

        return $maskedCardNumber;
    }

    /**
     * Set credit card data
     *
     * @param $data
     * @return void
     */
    private function setCreditCardData(&$data)
    {
        $data['cc_last_4'] = substr($data['cc_number'], -4);
        $data['cc_number'] = $this->maskCreditCardNumber($data['cc_number']);
    }

    /**
     * Update the subscription with the new payment profile
     *
     * @param int $subscriptionId
     * @param int $paymentProfileId
     * @throws \Exception
     */
    private function updateSubscriptionPaymentProfile($subscriptionId, $paymentProfileId)
    {
        $subscription = $this->subscriptionFactory->create();
        $this->subscriptionResource->load($subscription, $subscriptionId);

        if (!$subscription->getId()) {
            throw new \Exception(__('Subscription not found.'));
        }

        $subscription->setPaymentProfile($paymentProfileId);
        $this->subscriptionResource->save($subscription);
    }

    /**
     * Update Vindi payment profile
     *
     * @param int $paymentProfileId
     * @param int $subscriptionId
     * @return bool
     */
    private function updateVindiPaymentProfile($paymentProfileId, $subscriptionId)
    {
        try {
            $this->api->request('subscriptions/' . $subscriptionId, 'PUT', [
                'payment_profile' => [
                    'id' => $paymentProfileId
                ]
            ]);

            return true;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Failed to update Vindi payment profile: ') . '"' . $e->getMessage() . '"');
            return false;
        }
    }
}
