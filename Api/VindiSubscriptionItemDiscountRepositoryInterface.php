<?php
declare(strict_types=1);

namespace Vindi\Payment\Api;

use Vindi\Payment\Api\Data\VindiSubscriptionItemDiscountInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Vindi\Payment\Api\Data\VindiSubscriptionItemDiscountSearchResultInterface;

/**
 * Interface VindiSubscriptionItemDiscountRepositoryInterface
 * @package Vindi\Payment\Api
 */
interface VindiSubscriptionItemDiscountRepositoryInterface
{
    /**
     * Get subscription item discount by ID.
     *
     * @param int $entityId
     * @return VindiSubscriptionItemDiscountInterface
     */
    public function getById(int $entityId): VindiSubscriptionItemDiscountInterface;

    /**
     * Save subscription item discount.
     *
     * @param VindiSubscriptionItemDiscountInterface $vindiSubscriptionItemDiscount
     * @return void
     */
    public function save(VindiSubscriptionItemDiscountInterface $vindiSubscriptionItemDiscount): void;

    /**
     * Delete subscription item discount.
     *
     * @param VindiSubscriptionItemDiscountInterface $vindiSubscriptionItemDiscount
     * @return void
     */
    public function delete(VindiSubscriptionItemDiscountInterface $vindiSubscriptionItemDiscount): void;

    /**
     * Get list of subscription item discounts.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return VindiSubscriptionItemDiscountSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): VindiSubscriptionItemDiscountSearchResultInterface;
}
