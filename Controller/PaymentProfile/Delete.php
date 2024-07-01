<?php

namespace Vindi\Payment\Controller\PaymentProfile;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;
use Vindi\Payment\Api\PaymentProfileRepositoryInterface;
use Vindi\Payment\Model\Payment\Profile as PaymentProfileManager;
use Vindi\Payment\Model\PaymentProfileFactory;
use Vindi\Payment\Model\ResourceModel\Subscription\Collection as SubscriptionCollection;

class Delete extends Action
{
    protected $resultPageFactory;
    protected $customerSession;
    protected $paymentProfileFactory;
    protected $paymentProfileManager;
    protected $paymentProfileRepository;
    protected $subscriptionCollection;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param PaymentProfileFactory $paymentProfileFactory
     * @param PaymentProfileManager $paymentProfileManager
     * @param PaymentProfileRepositoryInterface $paymentProfileRepository
     * @param SubscriptionCollection $subscriptionCollection
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        PaymentProfileFactory $paymentProfileFactory,
        PaymentProfileManager $paymentProfileManager,
        PaymentProfileRepositoryInterface $paymentProfileRepository,
        SubscriptionCollection $subscriptionCollection
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->paymentProfileFactory = $paymentProfileFactory;
        $this->paymentProfileManager = $paymentProfileManager;
        $this->paymentProfileRepository = $paymentProfileRepository;
        $this->subscriptionCollection = $subscriptionCollection;
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
     * Execute the action
     *
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $paymentProfileId = $this->getRequest()->getParam('entity_id');
        if (!is_numeric($paymentProfileId) || $paymentProfileId <= 0) {
            $this->messageManager->addErrorMessage(__('Invalid payment profile ID.'));
            return $this->resultRedirectFactory->create()->setPath('vindi_vr/paymentprofile/index');
        }

        $paymentProfile = $this->paymentProfileRepository->getById($paymentProfileId);
        $paymentProfileVindiId = $paymentProfile->getData('payment_profile_id');

        $subscriptions = $this->subscriptionCollection->addFieldToFilter('payment_profile', $paymentProfileVindiId);
        if ($subscriptions->getSize() > 0) {
            $this->messageManager->addWarningMessage(__('The payment profile is being used in the following subscriptions:'));

            foreach ($subscriptions as $subscription) {
                $subscriptionId = $subscription->getId();
                $this->messageManager->addWarningMessage(__('Subscription ID: %1', $subscriptionId));
            }

            $this->messageManager->addWarningMessage(__('If you wish to delete this card, please associate a different existing card or register a new card and associate it with the current subscriptions.'));

            return $this->resultRedirectFactory->create()->setPath('vindi_vr/paymentprofile/index');
        }

        try {
            $this->paymentProfileManager->deletePaymentProfile($paymentProfile->getData('payment_profile_id'));
            $this->paymentProfileRepository->deleteById($paymentProfileId);
            $this->messageManager->addSuccessMessage(__('Payment profile successfully removed.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while removing the payment profile: ') . $e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('vindi_vr/paymentprofile/index');
    }
}
