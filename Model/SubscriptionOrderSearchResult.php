<?php
declare(strict_types=1);

namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\SubscriptionOrderSearchResultInterface;

use Magento\Framework\Api\SearchResults;

/**
 * Class SubscriptionOrderSearchResult
 * @package Vindi\Payment\Model
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class SubscriptionOrderSearchResult extends SearchResults implements SubscriptionOrderSearchResultInterface
{
}
