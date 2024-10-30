<?php
declare(strict_types=1);

namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\SubscriptionSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Class SubscriptionSearchResult
 * @package Vindi\Payment\Model
 */
class SubscriptionSearchResult extends SearchResults implements SubscriptionSearchResultsInterface
{
}
