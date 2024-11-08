<?php

namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Vindi\Payment\Model\Vindi\ProductItems;
use Vindi\Payment\Model\Vindi\ProductManagement;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class SaveAddNewItem
 *
 * @package Vindi\Payment\Controller\Adminhtml\Subscription
 */
class SaveAddNewItem extends Action
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var ProductManagement */
    protected $productManagement;

    /** @var ProductItems */
    protected $productItems;

    /** @var JsonFactory */
    protected $resultJsonFactory;

    /**
     * SaveAddNewItem constructor.
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param ProductManagement $productManagement
     * @param ProductItems $productItems
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        ProductManagement $productManagement,
        ProductItems $productItems,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->productManagement = $productManagement;
        $this->productItems = $productItems;
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

            $productId = $postData['product_id'] ?? null;
            $quantity  = $postData['quantity'] ?? null;
            $subscriptionId = $postData['subscription_id'] ?? null;

            if (!$productId || !$quantity || !$subscriptionId) {
                throw new LocalizedException(__('Missing required data: product_id, quantity, or subscription_id.'));
            }

            $product = $this->productRepository->getById($productId);
            $vindiProductId = $this->productManagement->findOrCreate($product);

            $data = [
                'product_id' => $vindiProductId,
                'subscription_id' => $subscriptionId,
                'quantity' => $quantity,
            ];

            $response = $this->productItems->createProductItem($data);

            if (!$response) {
                throw new LocalizedException(__('Failed to create new item in Vindi subscription.'));
            }

            $this->messageManager->addSuccessMessage(__('New item added to subscription successfully.'));

            return $resultRedirect->setPath('*/*/editsubscriptionitem', ['entity_id' => $subscriptionId]);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while adding the item to the subscription.'));
        }

        return $resultRedirect->setPath('*/*/editsubscriptionitem', ['entity_id' => $this->getRequest()->getParam('subscription_id')]);
    }
}
