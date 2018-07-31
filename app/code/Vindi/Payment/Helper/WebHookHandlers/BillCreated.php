<?php

namespace Vindi\Payment\Helper\WebHookHandlers;


class BillCreated
{
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle 'bill_created' event.
     * The bill can be related to a subscription or a single payment.
     *
     * @param array $data
     *
     * @return bool
     */
    public function billCreated($data)
    {
        if (!($bill = $data['bill'])) {
            $this->logger->error('Erro ao interpretar webhook "bill_created".');

            return false;
        }

        if (!isset($bill['subscription']) || is_null($bill['subscription'])) {
            $this->logger->info(sprintf('Ignorando o evento "bill_created" para venda avulsa.'));

            return false;
        }

        $period = intval($bill['period']['cycle']);

        if (isset($bill['period']) && ($period === 1)) {
            $this->logger->info(sprintf('Ignorando o evento "bill_created" para o primeiro ciclo.'));

            return false;
        }

        if (($order = $this->getOrder($data))) {
            $this->logger->warning(sprintf('Já existe o pedido %s para o evento "bill_created".', $order->getId()));

            return false;
        }

        $subscriptionId = $bill['subscription']['id'];
        $lastPeriodOrder = $this->getOrderForPeriod($subscriptionId, $period - 1);

        if (!$lastPeriodOrder || !$lastPeriodOrder->getId()) {
            $this->logger->warning('Pedido anterior não encontrado. Ignorando evento.');

            return false;
        }

        $vindiData = [
            'bill' => [
                'id' => $data['bill']['id'],
                'amout' => $data['bill']['amount']
            ],
            'products' => [],
            'shipping' => [],
        ];
        foreach ($data['bill']['bill_items'] as $billItem) {
            if ($billItem['product']['code'] == 'frete') {
                $vindiData['shipping'] = $billItem;
            } else {
                $vindiData['products'][] = $billItem;
            }
        }

        $order = $this->createOrder($lastPeriodOrder, $vindiData);

        if (!$order) {
            $this->logger->error('Impossível gerar novo pedido!');

            return false;
        }

        $this->logger->info(sprintf('Novo pedido gerado: %s.', $order->getId()));

        $order->setVindiSubscriptionId($subscriptionId);
        $order->setVindiSubscriptionPeriod($period);
        $order->save();

        if (Mage::getStoreConfig('vindi_subscription/general/bankslip_link_in_order_comment')) {
            foreach ($data['bill']['charges'] as $charge) {
                if ($charge['payment_method']['type'] == 'PaymentMethod::BankSlip') {
                    $order->addStatusHistoryComment(sprintf(
                        '<a target="_blank" href="%s">Clique aqui</a> para visualizar o boleto.',
                        $charge['print_url']
                    ))
                        ->setIsVisibleOnFront(true);
                    $order->save();
                }
            }
        }

        return true;
    }
}