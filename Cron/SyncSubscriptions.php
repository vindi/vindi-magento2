<?php

declare(strict_types=1);

namespace Vindi\Payment\Cron;

use Vindi\Payment\Model\Subscription\SyncSubscriptionInterface;

/**
 * Class SyncSubscriptions
 */
class SyncSubscriptions
{
    /**
     * @var SyncSubscriptionInterface
     */
    private $syncSubscription;

    /**
     * SyncSubscriptions constructor.
     * @param SyncSubscriptionInterface $syncSubscription
     */
    public function __construct(
        SyncSubscriptionInterface $syncSubscription
    ) {
        $this->syncSubscription = $syncSubscription;
    }

    public function execute()
    {
        $this->syncSubscription->execute();
    }
}
