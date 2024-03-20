<?php
declare(strict_types=1);

namespace Vindi\Payment\Model;

use Magento\Framework\Api\SearchResults;
use Vindi\Payment\Api\Data\PaymentProfileSearchResultInterface;

/**
 * Class PaymentProfileSearchResult
 * @package Vindi\Payment\Model
 */
class PaymentProfileSearchResult extends SearchResults implements PaymentProfileSearchResultInterface
{

}
