<?php

namespace Vindi\Payment\Api;

/**
 * Interface SubscriptionRepositoryInterface
 * @package Vindi\Payment\Api
 */
interface SubscriptionRepositoryInterface
{
    /**
     * Save Subscription
     * @param \Vindi\Payment\Api\Data\SubscriptionInterface $subscription
     * @return \Vindi\Payment\Api\Data\SubscriptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Vindi\Payment\Api\Data\SubscriptionInterface $subscription
    );

    /**
     * Retrieve Subscription
     * @param string $subscriptionId
     * @return \Vindi\Payment\Api\Data\SubscriptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($subscriptionId);

    /**
     * Retrieve Subscription matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Vindi\Payment\Api\Data\SubscriptionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );
}
