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
    }
}