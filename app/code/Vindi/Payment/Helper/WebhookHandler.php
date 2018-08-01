<?php

namespace Vindi\Payment\Helper;


use Vindi\Payment\Helper\WebHookHandlers\BillCreated;
use Vindi\Payment\Helper\WebHookHandlers\BillPaid;
use Vindi\Payment\Helper\WebHookHandlers\ChargeRejected;

class WebhookHandler
{

    public function __construct(
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Psr\Log\LoggerInterface $logger,
        BillCreated $billCreated,
        BillPaid $billPaid,
        ChargeRejected $chargeRejected
    )
    {
        $this->remoteAddress = $remoteAddress;
        $this->logger = $logger;
        $this->billCreated = $billCreated;
        $this->billPaid = $billPaid;
        $this->chargeRejected = $chargeRejected;
    }

    public function getRemoteIp()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $obj = $om->get('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress');
        $ip = $obj->getRemoteAddress();
        return $ip;
    }


    /**
     * Handle incoming webhook.
     *
     * @param string $body
     *
     * @return bool
     */
    public function handle($body)
    {
        try {
            $jsonBody = json_decode($body, true);

            if (!$jsonBody || !isset($jsonBody['event'])) {
                throw new \Exception('Evento do Webhook não encontrado!');
            }

            $type = $jsonBody['event']['type'];
            $data = $jsonBody['event']['data'];
        } catch (\Exception $e) {
            $this->log(sprintf('Falha ao interpretar JSON do webhook: %s', $e->getMessage()));
            return false;
        }

        switch ($type) {
            case 'test':
                $this->logger->info('Evento de teste do webhook.');
                exit('1');
            case 'bill_created':
                return $this->billCreated->billCreated($data);
            case 'bill_paid':
                return $this->billPaid->billPaid($data);
            case 'charge_rejected':
                return $this->chargeRejected->chargeRejected($data);
            default:
                $this->logger->warning(sprintf('Evento do webhook ignorado pelo plugin: "%s".', $type));
                exit('0');
        }
    }
}