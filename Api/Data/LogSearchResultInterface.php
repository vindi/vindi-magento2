<?php
declare(strict_types=1);

namespace Vindi\Payment\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface LogSearchResultInterface extends SearchResultsInterface
{
    public function getItems();
    public function setItems(array $items);
}
