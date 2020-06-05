<?php

declare(strict_types=1);

namespace Vindi\Payment\Model\Subscription;

use DateTime;
use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Vindi\Payment\Helper\Api;

/**
 * Class SyncSubscription
 */
class SyncSubscription implements SyncSubscriptionInterface
{
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
     * SyncSubscription constructor.
     * @param Api $api
     * @param ResourceConnection $resource
     */
    public function __construct(
        Api $api,
        ResourceConnection $resource
    ) {
        $this->api = $api;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function execute()
    {
        $subscriptions = $this->getSubscriptions();
        if (empty($subscriptions)) {
            return;
        }

        $data = $this->extractData($subscriptions);
        $this->saveData($data);

        return;
    }

    /**
     * @param int $page
     * @param array $subscription
     * @return array
     */
    private function getSubscriptions($page = 1, $subscription = [])
    {
        $endpoint = 'subscriptions?per_page='. self::LIMIT_PER_PAGE .'&page=' . $page;
        $request = $this->api->request($endpoint, 'GET');

        if (empty($request['subscriptions'])) {
            return $subscription;
        }

        $subscription = array_merge($subscription, $request['subscriptions']);

        return $this->getSubscriptions(++$page, $subscription);
    }

    /**
     * @param array $subscriptions
     * @return array
     * @throws Exception
     */
    private function extractData(array $subscriptions)
    {
        $data = [];

        foreach ($subscriptions as $key => $item) {
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

        return $data;
    }

    /**
     * @param array $data
     * @return void
     */
    private function saveData(array $data)
    {
        $tableName = $this->resource->getTableName('vindi_subscription');
        $this->connection->truncateTable($tableName);
        $this->connection->insertMultiple($tableName, $data);
    }
}
