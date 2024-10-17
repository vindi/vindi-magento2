<?php
declare(strict_types=1);

namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\VindiSubscriptionItemSearchResultInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Class VindiSubscriptionItemSearchResult
 * @package Vindi\Payment\Model
 */
class VindiSubscriptionItemSearchResult extends SearchResults implements VindiSubscriptionItemSearchResultInterface
{
}
