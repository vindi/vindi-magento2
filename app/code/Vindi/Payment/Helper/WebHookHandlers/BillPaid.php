<?php

namespace Vindi\Payment\Helper\WebHookHandlers;


use Magento\Sales\Model\Order\Invoice;

class BillPaid
{

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Order $order
    )
    {
        $this->logger = $logger;
        $this->order = $order;
    }

    /**
     * Handle 'bill_paid' event.
     * The bill can be related to a subscription or a single payment.
     *
     * @param array $data
     *
     * @return bool
     */
    public function billPaid($data)
    {
        if (!($order = $this->order->getOrder($data))) {
            $this->logger->error(
                sprintf(
                    'Ainda não existe um pedido para ciclo %s da assinatura: %d.',
                    $data['bill']['period']['cycle'],
                    $data['bill']['subscription']['id']
                )
            );

            return false;
        }

        return $this->createInvoice($order);
    }

    /**
     * @return bool
     */
    public function createInvoice($order)
    {
        if (!$order->getId()) {
            return false;
        }

        $this->logger->info(sprintf('Gerando fatura para o pedido: %s.', $order->getId()));

        $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true,
            'O pagamento foi confirmado e o pedido está sendo processado.', true);

        if (!$order->canInvoice()) {
            $this->logger->error(sprintf('Impossível gerar fatura para o pedido %s.', $order->getId()));

            return false;
        }

        $invoice = $order->prepareInvoice();
        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
        $invoice->register();
//        Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder())->save();
        $invoice->sendEmail(true);
        $this->logger->info('Fatura gerada com sucesso.');

        return true;
    }
}