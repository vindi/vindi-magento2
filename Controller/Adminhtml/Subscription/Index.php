<?php

namespace Vindi\Payment\Controller\Adminhtml\Subscription;

use DateTime;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Vindi\Payment\Helper\Api;

/**
 * Class Index
 * @package Vindi\Payment\Controller\Adminhtml\Subscription
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
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
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Api $api
     * @param ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Api $api,
        ResourceConnection $resource
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
        $this->api = $api;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        try {
            $this->sync();
        } catch (Exception $e) {
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("Subscriptions"));
        return $resultPage;
    }

    /**
     * @throws Exception
     */
    private function sync()
    {
        $data = [];

        $subscriptions = $this->getSubscriptions(1);
        if (!$subscriptions) {
            return;
        }

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

        $tableName = $this->resource->getTableName('vindi_subscription');
        $this->connection->truncateTable($tableName);
        $this->connection->insertMultiple($tableName, $data);
    }

    /**
     * @param int $page
     * @param array $subscription
     * @return array|mixed
     */
    private function getSubscriptions($page = 1, $subscription = [])
    {
        if ($page == 20) {
            return $subscription;
        }

        $request = $this->api->request('subscriptions?per_page=500&page=' . $page, 'GET');
        if (!empty($request['subscriptions'])) {
            $subscription = $request['subscriptions'];
            $this->getSubscriptions(++$page, $subscription);
        }

        return $subscription;
    }
}
