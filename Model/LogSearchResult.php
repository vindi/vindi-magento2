<?php
declare(strict_types=1);

namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\LogSearchResultInterface;
use Magento\Framework\Api\SearchResults;

class LogSearchResult extends SearchResults implements LogSearchResultInterface
{
}
