<?php

namespace Vindi\Payment\Helper\WebHookHandlers;


use Vindi\Payment\Model\Payment\Api;
use Vindi\Payment\Model\Payment\Bill;

class ChargeRejected
{
    private $bill, $order, $logger;

    public function __construct(
        Bill $bill,
        Order $order,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->bill = $bill;
        $this->order = $order;
        $this->logger = $logger;
    }

    /**
     * @param array $data
     *
     * @return bool
     * @throws \Exception
     */
    public function chargeRejected($data)
    {
        $charge = $data['charge'];

        if (!($order = $this->getOrderFromBill($charge['bill']['id']))) {
            $this->logger->warning('Pedido não encontrado.');

            return false;
        }

        $gatewayMessage = $charge['last_transaction']['gateway_message'];
        $isLastAttempt = is_null($charge['next_attempt']);

        if ($isLastAttempt) {
            $order->addStatusHistoryComment(sprintf(
                'Tentativa de Pagamento rejeitada. Motivo: "%s"',
                $gatewayMessage
            ));
            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true, sprintf(
                'Todas as tentativas de pagamento foram rejeitadas. Motivo: "%s".',
                $gatewayMessage
            ), true);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED, true, sprintf(
                'Todas as tentativas de pagamento foram rejeitadas. Motivo: "%s".',
                $gatewayMessage
            ), true);
            $this->logger->info(sprintf(
                'Todas as tentativas de pagamento do pedido %s foram rejeitadas. Motivo: "%s".',
                $order->getId(),
                $gatewayMessage
            ));
        } else {
            $order->addStatusHistoryComment(sprintf(
                'Tentativa de Pagamento rejeitada. Motivo: "%s". Uma nova tentativa será feita.',
                $gatewayMessage
            ));
            $this->logger->info(sprintf(
                'Tentativa de pagamento do pedido %s foi rejeitada. Motivo: "%s". Uma nova tentativa será feita.',
                $order->getId(),
                $gatewayMessage
            ));
        }

        $order->save();

        return true;
    }

    private function getOrderFromBill($billId)
    {
        $bill = $this->bill->getBill($billId);

        if (!$bill) {
            return false;
        }

        return $this->order->getOrder(compact('bill'));
    }
}