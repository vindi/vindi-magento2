<?php
declare(strict_types=1);

namespace Vindi\Payment\Api;

use Vindi\Payment\Api\Data\VindiSubscriptionItemInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Vindi\Payment\Api\Data\VindiSubscriptionItemSearchResultInterface;

/**
 * Interface VindiSubscriptionItemRepositoryInterface
 * @package Vindi\Payment\Api
 */
interface VindiSubscriptionItemRepositoryInterface
{
    /**
     * Get subscription item by ID.
     *
     * @param int $entityId
     * @return VindiSubscriptionItemInterface
     */
    public function getById(int $entityId): VindiSubscriptionItemInterface;

    /**
     * Save subscription item.
     *
     * @param VindiSubscriptionItemInterface $vindiSubscriptionItem
     * @return void
     */
    public function save(VindiSubscriptionItemInterface $vindiSubscriptionItem): void;

    /**
     * Delete subscription item.
     *
     * @param VindiSubscriptionItemInterface $vindiSubscriptionItem
     * @return void
     */
    public function delete(VindiSubscriptionItemInterface $vindiSubscriptionItem): void;

    /**
     * Get list of subscription items.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return VindiSubscriptionItemSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): VindiSubscriptionItemSearchResultInterface;
}
