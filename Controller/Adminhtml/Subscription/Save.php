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

            $request = $this->api->request('subscriptions/'.$id, 'PUT', [
               'payment_profile' => [
                   'id' => $data['payment_profile']
               ]
            ]);

            if (!is_array($request)) {
                $this->messageManager->addErrorMessage(__('This Subscription no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model = $this->_objectManager->create(Subscription::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the Subscription.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the Subscription.'));
                $this->dataPersistor->clear('vindi_payment_subscription');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Subscription.'));
            }

            $this->dataPersistor->set('vindi_payment_subscription', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
