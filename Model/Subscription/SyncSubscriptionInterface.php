<?php

declare(strict_types=1);

namespace Vindi\Payment\Model\Subscription;

/**
 * Interface SyncSubscriptionInterface
 */
interface SyncSubscriptionInterface
{
    const LIMIT_PER_PAGE = 50;

    /**
     * @return void
     */
    public function execute();
}
