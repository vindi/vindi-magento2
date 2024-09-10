<?php

namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Vindi\Payment\Model\Vindi\ProductItems;
use Vindi\Payment\Model\VindiSubscriptionItemRepository;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class SaveSubscriptionItem
 *
 * @package Vindi\Payment\Controller\Adminhtml\Subscription
 */
class SaveSubscriptionItem extends Action
{
    /** @var ProductItems */
    protected $productItems;

    /** @var VindiSubscriptionItemRepository */
    protected $subscriptionItemRepository;

    /** @var JsonFactory */
    protected $resultJsonFactory;

    /**
     * SaveSubscriptionItem constructor.
     * @param Context $context
     * @param ProductItems $productItems
     * @param VindiSubscriptionItemRepository $subscriptionItemRepository
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        ProductItems $productItems,
        VindiSubscriptionItemRepository $subscriptionItemRepository,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->productItems = $productItems;
        $this->subscriptionItemRepository = $subscriptionItemRepository;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultJson = $this->resultJsonFactory->create();
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $resultJson->setData(['error' => true, 'message' => __('Invalid request method.')]);
        }

        try {
            $postData = $request->getPostValue();

            $entityId = $postData['entity_id'] ?? null;
            $price    = $postData["settings"]["price"] ?? null;

            if (!$entityId || $price === null) {
                throw new LocalizedException(__('Missing required data: entity_id or price.'));
            }

            $subscriptionItem = $this->subscriptionItemRepository->getById($entityId);

            $data = [
                'pricing_schema' => [
                    'price' => $price
                ]
            ];

            $productItemId = $subscriptionItem->getProductItemId();
            $response = $this->productItems->updateProductItem($productItemId, $data);

            if (!$response) {
                throw new LocalizedException(__('Failed to update price on Vindi API.'));
            }

            $subscriptionItem->setPrice($price);
            $this->subscriptionItemRepository->save($subscriptionItem);

            $this->messageManager->addSuccessMessage(__('Price updated successfully.'));

            return $resultRedirect->setPath('*/*/editsubscriptionitem', ['entity_id' => $subscriptionItem->getId()]);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while saving the subscription item.'));
        }

        return $resultRedirect->setPath('*/*/editsubscriptionitem', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
    }
}
