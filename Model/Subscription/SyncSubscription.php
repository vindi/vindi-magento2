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
        $tableName = $this->resource->getTableName('vindi_subscription');

        $subscriptions = $this->getSubscriptions();

        if (empty($subscriptions)) {
            return;
        }

        $data = $this->extractData($subscriptions);
        $this->insertMultiple($tableName, $data);

        return;
    }

    /**
     * @return array
     */
    private function getSubscriptions(): array
    {
        $page = 1;
        $subscription = [];
        do {
            $hasMore = false;
            $endpoint = 'subscriptions?per_page=' . self::LIMIT_PER_PAGE . '&sort_by=created_at&sort_order=desc' . '&page=' . $page;
            $request = $this->api->request($endpoint, 'GET');

            if (!empty($request['subscriptions'])) {
                $subscription = array_merge($subscription, $request['subscriptions']);
                $hasMore = count($request['subscriptions']) == self::LIMIT_PER_PAGE;
            }

            $page++;
        } while ($hasMore);

        return $subscription;
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
            $subscriptionId = $item['id'];
            $subscription = $this->getSubscriptionById($subscriptionId);

            if ($subscription) {
                continue;
            }

            $customerEmail = isset($subscriptions[$key]["customer"]["email"]) ? $subscriptions[$key]["customer"]["email"] : null;

            if (!$customerEmail) {
                continue;
            }

            $customer = $this->getCustomer($customerEmail);

            if (!$customer) {
                continue;
            }

            $customerId = isset($customer['entity_id']) ? $customer['entity_id'] : null;

            $startAt = new DateTime($item['start_at']);

            $data[$key] = [
                'id'              => $item['id'],
                'client'          => $item['customer']['name'],
                'customer_email'  => $customerEmail,
                'customer_id'     => $customerId,
                'plan'            => $item['plan']['name'],
                'payment_method'  => $item['payment_method']['code'],
                'payment_profile' => null,
                'status'          => $item['status'],
                'start_at'        => $startAt->format('Y-m-d H:i:s')
            ];

            if (is_array($item['payment_profile'])) {
                $data[$key]['payment_profile'] = $item['payment_profile']['id'];
            }
        }

        return $data;
    }

    /**
     * @param $email
     * @return mixed
     */
    private function getCustomer($email)
    {
        return $this->connection->fetchRow(
            'SELECT entity_id FROM customer_entity WHERE email = :email',
            [':email' => $email]
        );
    }

    /**
     * @param $id
     * @return mixed
     */
    private function getSubscriptionById($id)
    {
        return $this->connection->fetchRow(
            'SELECT id FROM vindi_subscription WHERE id = :id',
            [':id' => $id]
        );
    }

    /**
     * @param $tableName
     */
    private function truncateTable($tableName)
    {
        $this->connection->truncateTable($tableName);
    }

    /**
     * @param $tableName
     * @param $data
     */
    private function insertMultiple($tableName, $data)
    {
        if (empty($data)) {
            return;
        }

        $this->connection->insertMultiple($tableName, $data);
    }
}
