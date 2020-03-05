<?php

namespace Vindi\Payment\Cron;

use DateTime;
use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Psr\Log\LoggerInterface;
use Vindi\Payment\Helper\Api;

/**
 * Class SubscriptionSync
 * @package Vindi\Payment\Cron
 */
class SubscriptionSync
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Api
     */
    private $api;
    /**
     * @var ResourceConnection
     */
    private $resource;
    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * SubscriptionSync constructor.
     * @param LoggerInterface $logger
     * @param ResourceConnection $resource
     * @param Api $api
     */
    public function __construct(
        LoggerInterface $logger,
        ResourceConnection $resource,
        Api $api
    ) {
        $this->logger = $logger;
        $this->api = $api;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
    }

    public function execute()
    {
        $this->logger->addInfo("VINDI Subscription Sync is executed.");

        $data = [];

        $request = $this->api->request('subscriptions', 'GET');
        if (empty($request['subscriptions'])) {
            die('Done');
        }

        foreach ($request['subscriptions'] as $key => $item) {
            $startAt = new DateTime($item['start_at']);

            $data[$key] = [
                'id' => $item['id'],
                'client' => $item['customer']['name'],
                'plan' => $item['plan']['name'],
                'payment_method' => $item['payment_method']['code'],
                'payment_profile' => null,
                'status' => $item['status'],
                'start_at' => $startAt->format('Y-m-d H:i:s')
            ];

            if (is_array($item['payment_profile'])) {
                $data[$key]['payment_profile'] = $item['payment_profile']['id'];
            }
        }

        try {
            $tableName = $this->resource->getTableName('vindi_subscription');
            $this->connection->truncateTable($tableName);
            $this->connection->insertMultiple($tableName, $data);
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        die('Done');
    }
}

