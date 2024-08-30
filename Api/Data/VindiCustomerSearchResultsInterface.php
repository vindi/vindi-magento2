<?php
namespace Vindi\Payment\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface VindiCustomerSearchResultsInterface
 *
 * Provides list of Vindi Customers.
 */
interface VindiCustomerSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get list of Vindi Customers
     *
     * @return VindiCustomerInterface[]
     */
    public function getItems();

    /**
     * Set list of Vindi Customers
     *
     * @param VindiCustomerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
