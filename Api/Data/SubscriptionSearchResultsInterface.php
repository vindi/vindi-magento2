<?php

namespace Vindi\Payment\Api\Data;

/**
 * Interface SubscriptionSearchResultsInterface
 * @package Vindi\Payment\Api\Data
 */
interface SubscriptionSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get Subscription list.
     * @return \Vindi\Payment\Api\Data\SubscriptionInterface[]
     */
    public function getItems();

    /**
     * Set client list.
     * @param \Vindi\Payment\Api\Data\SubscriptionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

