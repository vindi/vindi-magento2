<?php
declare(strict_types=1);

namespace Vindi\Payment\Api;

use Vindi\Payment\Api\Data\VindiPlanInterface;
use Vindi\Payment\Api\Data\VindiPlanSearchResultInterface;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface VindiPlanRepositoryInterface
 * @package Vindi\Payment\Api
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
interface VindiPlanRepositoryInterface
{
    /**
     * @param int $entityId
     * @return VindiPlanInterface
     */
    public function getById(int $entityId): VindiPlanInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return VindiPlanSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): VindiPlanSearchResultInterface;

    /**
     * @param VindiPlanInterface $vindiplan
     */
    public function save(VindiPlanInterface $vindiplan): void;

    /**
     * @param VindiPlanInterface $vindiplan
     */
    public function delete(VindiPlanInterface $vindiplan): void;
}
