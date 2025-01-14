<?php

declare(strict_types=1);

namespace Vindi\Payment\Console\Command;

use Vindi\Payment\Cron\CancelOrdersWithExpiredLinks;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;

class RunCancelOrdersWithExpiredLinks extends Command
{
    /**
     * @var CancelOrdersWithExpiredLinks
     */
    private CancelOrdersWithExpiredLinks $cancelOrdersWithExpiredLinks;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param CancelOrdersWithExpiredLinks $cancelOrdersWithExpiredLinks
     * @param LoggerInterface $logger
     */
    public function __construct(
        CancelOrdersWithExpiredLinks $cancelOrdersWithExpiredLinks,
        LoggerInterface $logger
    ) {
        $this->cancelOrdersWithExpiredLinks = $cancelOrdersWithExpiredLinks;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('vindi:payment:cancel-orders-with-expired-links');
        $this->setDescription('Manually run the cron to cancel orders with expired payment links');
        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->cancelOrdersWithExpiredLinks->execute();
            $output->writeln('<info>Orders with expired payment links have been canceled successfully.</info>');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->logger->error('Error while canceling orders with expired payment links: ' . $e->getMessage());
            $output->writeln('<error>An error occurred while canceling orders with expired payment links.</error>');
            return Command::FAILURE;
        }
    }
}
