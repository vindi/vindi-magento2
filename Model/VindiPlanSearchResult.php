<?php
declare(strict_types=1);

namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\VindiPlanSearchResultInterface;

use Magento\Framework\Api\SearchResults;

/**
 * Class VindiPlanSearchResult
 * @package Vindi\Payment\Model
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class VindiPlanSearchResult extends SearchResults implements VindiPlanSearchResultInterface
{
}
