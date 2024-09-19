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
 */

namespace Vindi\Payment\Controller\Adminhtml\PaymentLink;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Vindi\Payment\Model\PaymentLinkService;
use Magento\Sales\Api\OrderRepositoryInterface;
use Vindi\Payment\Block\Adminhtml\Order\LinkField;

class MassSend extends Action
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
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * Maximum number of orders allowed for processing at once.
     */
    private const MAX_ORDERS = 50;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param PaymentLinkService $paymentLinkService
     * @param LoggerInterface $logger
     * @param Validator $formKeyValidator
     * @param ManagerInterface $messageManager
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PaymentLinkService $paymentLinkService,
        LoggerInterface $logger,
        Validator $formKeyValidator,
        ManagerInterface $messageManager,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->paymentLinkService = $paymentLinkService;
        $this->logger = $logger;
        $this->formKeyValidator = $formKeyValidator;
        $this->messageManager = $messageManager;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Execute mass action for sending payment link
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $orderIds = $this->getRequest()->getParam('selected', []);

        if (count($orderIds) > self::MAX_ORDERS) {
            $this->messageManager->addErrorMessage(__('You can only select up to %1 orders at a time.', self::MAX_ORDERS));
            return $resultRedirect->setPath('sales/order/index');
        }

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid Form Key'));
            return $resultRedirect->setPath('sales/order/index');
        }

        try {
            $errors = 0;
            foreach ($orderIds as $orderId) {
                try {
                    $order = $this->orderRepository->get($orderId);
                    $paymentMethod = $order->getPayment()->getMethod();

                    if (!str_contains($paymentMethod, LinkField::VINDI_PAYMENT_LINK)) {
                        continue;
                    }

                    $success = $this->paymentLinkService->sendPaymentLinkEmail($order->getEntityId());
                    if (!$success) {
                        $errors++;
                    }
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                    $errors++;
                }
            }

            if ($errors === 0) {
                $this->messageManager->addSuccessMessage(__('The payment link was successfully sent for all selected orders.'));
            } else {
                $this->messageManager->addErrorMessage(__('%1 orders failed to send the payment link.', $errors));
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('Error sending the payment link.'));
        }

        return $resultRedirect->setPath('sales/order/index');
    }
}
