<?php
declare(strict_types=1);

namespace Vindi\Payment\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface VindiPlanSearchResultInterface
 * @package Vindi\Payment\Api\Data

 */
interface VindiPlanSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return VindiPlanInterface[]
     */
    public function getItems();

    /**
     * @param VindiPlanInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
