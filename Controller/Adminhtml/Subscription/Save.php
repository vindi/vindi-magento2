<?php

namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Vindi\Payment\Helper\Api;
use Vindi\Payment\Model\Subscription;

/**
 * Class Save
 *
 * @package Vindi\Payment\Controller\Adminhtml\Subscription
 */
class Save extends Action
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var Api
     */
    private $api;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param Api $api
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        Api $api
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
        $this->api = $api;
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = $this->getRequest()->getParam('id');
            if (!$id) {
                $this->messageManager->addErrorMessage(__('Invalid Subscription ID.'));
                return $resultRedirect->setPath('*/*/');
            }

            if (empty($data['payment_settings']['payment_profile'])) {
                $this->messageManager->addWarningMessage(__('Only credit card subscriptions can have a card registered! If you want to change the item, click edit in the grid listing.'));
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }

            try {
                $request = $this->api->request('subscriptions/' . $id, 'PUT', [
                    'payment_profile' => [
                        'id' => $data["payment_settings"]["payment_profile"]
                    ]
                ]);
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('API request failed: %1', $e->getMessage()));
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }

            if (!is_array($request)) {
                $this->messageManager->addErrorMessage(__('This Subscription no longer exists or API request failed.'));
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }

            $model = $this->_objectManager->create(Subscription::class)->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('Subscription with ID %1 does not exist.', $id));
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }

            $model->setData('payment_profile', $data["payment_settings"]["payment_profile"]);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the subscription.'));
                $this->dataPersistor->clear('vindi_payment_subscription');

                return $this->getRequest()->getParam('back')
                    ? $resultRedirect->setPath('*/*/edit', ['id' => $id])
                    : $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Subscription.'));
            }

            $this->dataPersistor->set('vindi_payment_subscription', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
