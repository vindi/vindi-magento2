<?php

namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Vindi\Payment\Model\Vindi\Discounts;
use Vindi\Payment\Model\VindiSubscriptionItemDiscountFactory;
use Vindi\Payment\Api\VindiSubscriptionItemDiscountRepositoryInterface;

/**
 * Class DeleteDiscount
 *
 * Controller for deleting subscription item discounts.
 */
class DeleteDiscount extends Action
{
    /** @var RedirectFactory */
    protected $resultRedirectFactory;

    /** @var ManagerInterface */
    protected $messageManager;

    /** @var Discounts */
    protected $discounts;

    /** @var VindiSubscriptionItemDiscountRepositoryInterface */
    protected $discountRepository;

    /** @var VindiSubscriptionItemDiscountFactory */
    protected $discountFactory;

    /**
     * @param Context $context
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     * @param Discounts $discounts
     * @param VindiSubscriptionItemDiscountRepositoryInterface $discountRepository
     * @param VindiSubscriptionItemDiscountFactory $discountFactory
     */
    public function __construct(
        Context $context,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        Discounts $discounts,
        VindiSubscriptionItemDiscountRepositoryInterface $discountRepository,
        VindiSubscriptionItemDiscountFactory $discountFactory
    ) {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->discounts = $discounts;
        $this->discountRepository = $discountRepository;
        $this->discountFactory = $discountFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $request = $this->getRequest();
        $discountId = $request->getParam('entity_id');
        $subscriptionId = null;

        if (!$discountId) {
            $this->messageManager->addErrorMessage(__('Missing required parameters.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $discount = $this->discountRepository->getById($discountId);
            $subscriptionId = $discount->getSubscriptionId();
            $vindiDiscountId = $discount->getVindiDiscountId();

            if (!$subscriptionId) {
                throw new LocalizedException(__('Subscription ID is missing for the discount.'));
            }

            if ($vindiDiscountId) {
                $isDeleted = $this->discounts->deleteDiscount($vindiDiscountId);
                if (!$isDeleted) {
                    throw new LocalizedException(__('Failed to delete the discount from the subscription in the API.'));
                }
            }

            $this->discountRepository->delete($discount);
            $this->eventManager->dispatch('vindi_subscription_update', ['subscription_id' => $subscriptionId]);
            $this->messageManager->addSuccessMessage(__('The discount was successfully deleted from the subscription.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An unexpected error occurred while deleting the discount.'));
        }

        return $resultRedirect->setPath('vindi_payment/subscription/edit', ['id' => $subscriptionId]);
    }
}
