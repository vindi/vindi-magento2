<?php

namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Registry;
use Vindi\Payment\Api\VindiSubscriptionItemDiscountRepositoryInterface;

/**
 * Class EditDiscount
 *
 * Controller for editing a subscription item discount.
 */
class EditDiscount extends Action
{
    /** @var RedirectFactory */
    protected $resultRedirectFactory;

    /** @var Registry */
    protected $registry;

    /** @var VindiSubscriptionItemDiscountRepositoryInterface */
    protected $discountRepository;

    /**
     * EditDiscount constructor.
     *
     * @param Action\Context $context
     * @param RedirectFactory $resultRedirectFactory
     * @param Registry $registry
     * @param VindiSubscriptionItemDiscountRepositoryInterface $discountRepository
     */
    public function __construct(
        Action\Context $context,
        RedirectFactory $resultRedirectFactory,
        Registry $registry,
        VindiSubscriptionItemDiscountRepositoryInterface $discountRepository
    ) {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->registry = $registry;
        $this->discountRepository = $discountRepository;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $discountId = $this->getRequest()->getParam('entity_id');

        if (!$discountId) {
            $this->messageManager->addErrorMessage(__('Missing required discount ID.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $discount = $this->discountRepository->getById($discountId);
            $this->registry->register('current_discount', $discount);

            return $resultRedirect->setPath('vindi_payment/subscription/adddiscount', ['id' => $discount->getSubscriptionId()]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while loading the discount.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
