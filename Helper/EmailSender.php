<?php

namespace Vindi\Payment\Helper;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class EmailSender
{
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * EmailSender constructor.
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Sends an email notifying the customer that the QR Code or Bank Slip is available for payment.
     *
     * @param Order $order
     * @return void
     */
    public function sendQrCodeAvailableEmail(Order $order)
    {
        try {
            $this->inlineTranslation->suspend();

            $store = $this->storeManager->getStore();
            $customerEmail = $order->getCustomerEmail();
            $customerName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
            $orderId = $order->getId();
            $orderLink = $store->getUrl('sales/order/view', ['order_id' => $orderId]);

            $templateVars = [
                'store' => $store,
                'customer_name' => $customerName,
                'order_id' => $orderId,
                'order_link' => $orderLink
            ];

            $from = [
                'email' => $store->getConfig('trans_email/ident_sales/email'),
                'name' => $store->getConfig('trans_email/ident_sales/name')
            ];

            $this->transportBuilder
                ->setTemplateIdentifier('vindi_payment_qrcode_available_email_template') // ID of Email template
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $store->getId(),
                ])
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($customerEmail, $customerName);

            $transport = $this->transportBuilder->getTransport();
            $message = $transport->getMessage();
            $message->setSubject(__('Your QRCode and/or BankSlip is now available for payment.'));

            $transport->sendMessage();

            $this->inlineTranslation->resume();
            $this->logger->info(__('Email notification for QR Code availability sent to customer.'));
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->logger->error(__('Error sending QR Code availability email: %1', $e->getMessage()));
        }
    }
}
