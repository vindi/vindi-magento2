<?php

namespace Vindi\Payment\Console\Command;

use Vindi\Payment\Cron\ProcessOrderPaidQueue;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;

/**
 * Command to execute the vindi_payment_process_order_paid_queue cron job
 */
class ProcessOrderPaidQueueCommand extends Command
{
    /**
     * @var ProcessOrderPaidQueue
     */
    private $processOrderPaidQueue;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param ProcessOrderPaidQueue $processOrderPaidQueue
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProcessOrderPaidQueue $processOrderPaidQueue,
        LoggerInterface $logger
    ) {
        $this->processOrderPaidQueue = $processOrderPaidQueue;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * Configure the command options and description
     */
    protected function configure()
    {
        $this->setName('vindi:process-order-paid-queue')
            ->setDescription('Executes the vindi_payment_process_order_paid_queue cron job manually.');
        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->processOrderPaidQueue->execute();
            $output->writeln('<info>Order paid queue processing executed successfully.</info>');
            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $this->logger->error('Error executing order paid queue processing: ' . $e->getMessage());
            $output->writeln('<error>Error executing order paid queue processing: ' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }
    }
}
