<?php
declare(strict_types=1);

namespace Vindi\Payment\Api;

use Vindi\Payment\Api\Data\VindiPlanItemInterface;
use Vindi\Payment\Api\Data\VindiPlanItemSearchResultInterface;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface VindiPlanItemRepositoryInterface
 * @package Vindi\Payment\Api
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
interface VindiPlanItemRepositoryInterface
{
    /**
     * @param int $entityId
     * @return VindiPlanItemInterface
     */
    public function getById(int $entityId): VindiPlanItemInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return VindiPlanItemSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): VindiPlanItemSearchResultInterface;

    /**
     * @param VindiPlanItemInterface $vindiplanitem
     */
    public function save(VindiPlanItemInterface $vindiplanitem): void;

    /**
     * @param VindiPlanItemInterface $vindiplanitem
     */
    public function delete(VindiPlanItemInterface $vindiplanitem): void;
}
