<?php

namespace Vindi\Payment\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateSubscriptionCommand
 *
 * Executes the observer to update a subscription by ID.
 */
class UpdateSubscriptionCommand extends Command
{
    /**
     * Argument name for subscription ID
     */
    private const ARGUMENT_SUBSCRIPTION_ID = 'subscription_id';

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var State
     */
    private $state;

    /**
     * Constructor
     *
     * @param EventManager $eventManager
     * @param State $state
     */
    public function __construct(
        EventManager $eventManager,
        State $state
    ) {
        $this->eventManager = $eventManager;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('vindi:subscription:update')
            ->setDescription('Updates a Vindi subscription by ID.')
            ->addArgument(
                self::ARGUMENT_SUBSCRIPTION_ID,
                InputArgument::REQUIRED,
                'ID of the subscription to update'
            );
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
            $this->state->setAreaCode('adminhtml');

            $subscriptionId = $input->getArgument(self::ARGUMENT_SUBSCRIPTION_ID);

            if (!is_numeric($subscriptionId)) {
                $output->writeln('<error>Invalid subscription ID. It must be numeric.</error>');
                return Command::FAILURE;
            }

            $this->eventManager->dispatch(
                'vindi_subscription_update',
                ['subscription_id' => (int)$subscriptionId]
            );

            $output->writeln('<info>Subscription with ID ' . $subscriptionId . ' has been updated successfully.</info>');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
