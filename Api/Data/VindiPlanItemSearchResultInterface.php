<?php
declare(strict_types=1);

namespace Vindi\Payment\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface VindiPlanItemSearchResultInterface
 * @package Vindi\Payment\Api\Data
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
interface VindiPlanItemSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return VindiPlanItemInterface[]
     */
    public function getItems();

    /**
     * @param VindiPlanItemInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
