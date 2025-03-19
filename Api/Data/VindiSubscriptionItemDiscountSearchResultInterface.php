<?php
declare(strict_types=1);

namespace Vindi\Payment\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface VindiSubscriptionItemDiscountSearchResultInterface
 *
 * Provides a contract for the search result of subscription item discounts.
 */
interface VindiSubscriptionItemDiscountSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get list of subscription item discounts.
     *
     * @return \Vindi\Payment\Api\Data\VindiSubscriptionItemDiscountInterface[]
     */
    public function getItems();

    /**
     * Set list of subscription item discounts.
     *
     * @param \Vindi\Payment\Api\Data\VindiSubscriptionItemDiscountInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
