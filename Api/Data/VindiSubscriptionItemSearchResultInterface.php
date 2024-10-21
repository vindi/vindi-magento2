<?php
declare(strict_types=1);

namespace Vindi\Payment\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface VindiSubscriptionItemSearchResultInterface
 * @package Vindi\Payment\Api\Data
 */
interface VindiSubscriptionItemSearchResultInterface extends SearchResultsInterface
{
    public function getItems();

    public function setItems(array $items);
}
