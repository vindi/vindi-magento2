<?php

namespace Vindi\Payment\Controller\Pix;

use Magento\Framework\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\Action;
use Vindi\Payment\Helper\Api;
use Magento\Sales\Model\OrderRepository;

class Renew extends Action
{
    /**
     * @var Api
     */
    private $api;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * Cancel constructor.
     * @param Context $context
     * @param Api $api
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        Context $context,
        Api $api,
        OrderRepository $orderRepository
    ) {
        parent::__construct($context);
        $this->api = $api;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();

        $billId = $this->getRequest()->getParam('bill');
        $orderId = $this->getRequest()->getParam('order');
        $messageError = "Não foi possivel atualizar o QR Code. Tente novamente mais tarde.";
        if ($billId) {
            try {
                $response = $this->api->request("bills/{$billId}" , "GET");
                if (count($response['bill']['charges']) > 0 ){
                    foreach ($response['bill']['charges'] as $charge) {
                        if ($charge['status'] == "pending") {
                            $chargeId = $charge['id'];
                            $newCharges = $this->api->request("charges/{$chargeId}/charge", "POST");
                            foreach($newCharges as $newCharge) {
                                if ($newCharge['status'] == 'pending' && isset($newCharge['last_transaction']['gateway_response_fields']['qrcode_original_path'])) {
                                    $order = $this->orderRepository->get($orderId);
                                    $additionalInformation = $order->getPayment()->getAdditionalInformation();
                                    $additionalInformation['qrcode_original_path'] = $newCharge['last_transaction']['gateway_response_fields']['qrcode_original_path'];
                                    $additionalInformation['qrcode_path'] = $newCharge['last_transaction']['gateway_response_fields']['qrcode_path'];
                                    $additionalInformation['max_days_to_keep_waiting_payment'] = $newCharge['last_transaction']['gateway_response_fields']['max_days_to_keep_waiting_payment'];
                                    $order->getPayment()->setAdditionalInformation($additionalInformation);
                                    $this->orderRepository->save($order);
                                    $this->messageManager->addSuccessMessage(__("QR Code atualizado"));
                                    return $resultRedirect;
                                } else {
                                    $this->messageManager->addErrorMessage(__($messageError));
                                }
                            }
                        }
                    }
                } else {
                    $this->messageManager->addErrorMessage(__($messageError));
                }
            } catch(\Exception $e){
                $this->messageManager->addErrorMessage(__($messageError));
            }
        } else {
            $this->messageManager->addErrorMessage(__($messageError));
        }
        return $resultRedirect;
    }
}
