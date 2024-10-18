<?php

declare(strict_types=1);

/**
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Vindi
 * @package     Vindi_Payment
 *
 *
 */

namespace Vindi\Payment\Controller\Adminhtml\PaymentLink;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Vindi\Payment\Model\PaymentLinkService;

class Send extends Action implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var PaymentLinkService
     */
    private PaymentLinkService $paymentLinkService;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Validator
     */
    private Validator $formKeyValidator;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param PaymentLinkService $paymentLinkService
     * @param LoggerInterface $logger
     * @param Validator $formKeyValidator
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PaymentLinkService $paymentLinkService,
        LoggerInterface $logger,
        Validator $formKeyValidator,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->paymentLinkService = $paymentLinkService;
        $this->logger = $logger;
        $this->formKeyValidator = $formKeyValidator;
        $this->messageManager = $messageManager;
    }

    /**
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $response = ['success' => false];
        $orderId = $this->getRequest()->getParam('order_id');

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid Form Key'));
            return $result->setData($response);
        }

        try {
            if ($orderId){
                $response['success'] = $this->paymentLinkService->sendPaymentLinkEmail($orderId);
            }

            if ($response['success']) {
                $this->messageManager->addSuccessMessage(__('The payment link was successfully sent.'));
            } else {
                $this->messageManager->addErrorMessage(__('Error to send the payment link.'));
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('Error to send the payment link.'));
        }

        return $result->setData($response);
    }
}
