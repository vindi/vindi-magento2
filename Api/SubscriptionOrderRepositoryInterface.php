<?php
declare(strict_types=1);

namespace Vindi\Payment\Api;

use Vindi\Payment\Api\Data\SubscriptionOrderInterface;
use Vindi\Payment\Api\Data\SubscriptionOrderSearchResultInterface;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface SubscriptionOrderRepositoryInterface
 * @package Vindi\Payment\Api
 */
interface SubscriptionOrderRepositoryInterface
{
    /**
     * @param int $entityId
     * @return SubscriptionOrderInterface
     */
    public function getById(int $entityId): SubscriptionOrderInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SubscriptionOrderSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SubscriptionOrderSearchResultInterface;

    /**
     * @param SubscriptionOrderInterface $subscriptionorder
     */
    public function save(SubscriptionOrderInterface $subscriptionorder): void;

    /**
     * @param SubscriptionOrderInterface $subscriptionorder
     */
    public function delete(SubscriptionOrderInterface $subscriptionorder): void;
}
