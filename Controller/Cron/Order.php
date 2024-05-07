<?php

namespace Vindi\Payment\Controller\Cron;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use Vindi\Payment\Cron\ProcessOrderCreationQueue;

class Order implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var ProcessOrderCreationQueue
     */
    private $processOrderCreationQueue;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * File lock path
     */
    private $lockFilePath;

    /**
     * Constructor
     *
     * @param JsonFactory $jsonFactory
     * @param ProcessOrderCreationQueue $processOrderCreationQueue
     * @param LoggerInterface $logger
     */
    public function __construct(
        JsonFactory $jsonFactory,
        ProcessOrderCreationQueue $processOrderCreationQueue,
        LoggerInterface $logger
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->processOrderCreationQueue = $processOrderCreationQueue;
        $this->logger = $logger;
        $this->lockFilePath = sys_get_temp_dir() . '/vindi_payment_process_order_creation_queue.lock';
    }

    /**
     * Execute the cron task manually via frontend controller.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();
        $lockHandle = fopen($this->lockFilePath, 'c+');

        if (flock($lockHandle, LOCK_EX | LOCK_NB)) {
            try {
                $this->processOrderCreationQueue->execute();
                $message = 'Successfully processed the order creation queue.';
                $result->setData(['status' => 'success', 'message' => $message]);
            } catch (\Exception $e) {
                $errorMessage = __('Error processing order creation queue: %1', $e->getMessage());
                $this->logger->error($errorMessage);
                $result->setData(['status' => 'error', 'message' => $errorMessage]);
            }

            flock($lockHandle, LOCK_UN);
        } else {
            $message = 'Process already running. Skipping execution.';
            $result->setData(['status' => 'info', 'message' => $message]);
        }

        fclose($lockHandle);
        return $result;
    }
}
