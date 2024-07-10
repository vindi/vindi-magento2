<?php
namespace Vindi\Payment\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Vindi\Payment\Api\Data\VindiCustomerInterface;
use Vindi\Payment\Api\Data\VindiCustomerSearchResultsInterface;

/**
 * Interface VindiCustomerRepositoryInterface
 *
 * Repository interface for Vindi Customer entity.
 */
interface VindiCustomerRepositoryInterface
{
    /**
     * Save Vindi Customer
     *
     * @param VindiCustomerInterface $vindiCustomer
     * @return VindiCustomerInterface
     */
    public function save(VindiCustomerInterface $vindiCustomer);

    /**
     * Get Vindi Customer by ID
     *
     * @param int $id
     * @return VindiCustomerInterface
     */
    public function getById($id);

    /**
     * Delete Vindi Customer
     *
     * @param VindiCustomerInterface $vindiCustomer
     * @return bool
     */
    public function delete(VindiCustomerInterface $vindiCustomer);

    /**
     * Get list of Vindi Customers
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return VindiCustomerSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
