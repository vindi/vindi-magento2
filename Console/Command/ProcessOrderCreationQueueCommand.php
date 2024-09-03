<?php

namespace Vindi\Payment\Console\Command;

use Vindi\Payment\Cron\ProcessOrderCreationQueue;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Psr\Log\LoggerInterface;

/**
 * Command to execute the vindi_payment_process_order_creation_queue cron job
 */
class ProcessOrderCreationQueueCommand extends Command
{
    /**
     * @var ProcessOrderCreationQueue
     */
    private $processOrderCreationQueue;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param ProcessOrderCreationQueue $processOrderCreationQueue
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProcessOrderCreationQueue $processOrderCreationQueue,
        LoggerInterface $logger
    ) {
        $this->processOrderCreationQueue = $processOrderCreationQueue;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * Configure the command options and description
     */
    protected function configure()
    {
        $this->setName('vindi:process-order-creation-queue')
            ->setDescription('Executes the vindi_payment_process_order_creation_queue cron job manually.');
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
            $this->processOrderCreationQueue->execute();
            $output->writeln('<info>Order creation queue processing executed successfully.</info>');
            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $this->logger->error('Error executing order creation queue processing: ' . $e->getMessage());
            $output->writeln('<error>Error executing order creation queue processing: ' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }
    }
}
