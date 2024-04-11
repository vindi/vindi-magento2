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

class Delete extends Action
{
    protected $resultPageFactory;

    protected $customerSession;
    protected $paymentProfileFactory;
    protected $paymentProfileManager;

    protected $paymentProfileRepository;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        PaymentProfileFactory $paymentProfileFactory,
        PaymentProfileManager $paymentProfileManager,
        PaymentProfileRepositoryInterface $paymentProfileRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->paymentProfileFactory = $paymentProfileFactory;
        $this->paymentProfileManager = $paymentProfileManager;
        $this->paymentProfileRepository = $paymentProfileRepository;
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
