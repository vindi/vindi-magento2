<?php

namespace Vindi\Payment\Controller\Cron;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;

class OrderById implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param JsonFactory $jsonFactory
     * @param OrderFactory $orderFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        JsonFactory $jsonFactory,
        OrderFactory $orderFactory,
        LoggerInterface $logger
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->orderFactory = $orderFactory;
        $this->logger = $logger;
    }

    /**
     * Load an order by its ID via frontend controller and compare with another order.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();

        $orderId1 = 35;
        $orderId2 = 42;

        if ($orderId1 <= 0 || $orderId2 <= 0) {
            $errorMessage = __('Invalid order IDs provided.');
            $this->logger->error($errorMessage);
            $result->setData(['status' => 'error', 'message' => $errorMessage]);
            return $result;
        }

        try {
            $order1 = $this->orderFactory->create()->load($orderId1);
            $order2 = $this->orderFactory->create()->load($orderId2);

            if (!$order1->getId() || !$order2->getId()) {
                $errorMessage = __('One or both orders not found.');
                $this->logger->error($errorMessage);
                $result->setData(['status' => 'error', 'message' => $errorMessage]);
                return $result;
            }

            // Compare orders and get differences
            $differences = $this->compareOrders($order1->getData(), $order2->getData());

            if (empty($differences)) {
                $result->setData(['status' => 'success', 'message' => 'Orders are identical']);
            } else {
                $result->setData(['status' => 'error', 'message' => 'Orders have differences', 'differences' => $differences]);
            }

        } catch (\Exception $e) {
            $errorMessage = __('Error loading or comparing orders: %1', $e->getMessage());
            $this->logger->error($errorMessage);
            $result->setData(['status' => 'error', 'message' => $errorMessage]);
        }

        return $result;
    }

    /**
     * Compare two orders and return differences.
     *
     * @param array $orderData1
     * @param array $orderData2
     * @return array
     */
    private function compareOrders(array $orderData1, array $orderData2)
    {
        $differences = [];

        foreach ($orderData1 as $key => $value) {
            if (array_key_exists($key, $orderData2)) {
                if ($value !== $orderData2[$key]) {
                    $differences[$key] = [
                        'order1' => $value,
                        'order2' => $orderData2[$key]
                    ];
                }
            } else {
                $differences[$key] = [
                    'order1' => $value,
                    'order2' => 'Key not found in order2'
                ];
            }
        }

        // Check for keys in order2 that are not in order1
        foreach ($orderData2 as $key => $value) {
            if (!array_key_exists($key, $orderData1)) {
                $differences[$key] = [
                    'order1' => 'Key not found in order1',
                    'order2' => $value
                ];
            }
        }

        return $differences;
    }
}
