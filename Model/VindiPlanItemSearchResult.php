<?php
declare(strict_types=1);

namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\VindiPlanItemSearchResultInterface;

use Magento\Framework\Api\SearchResults;

/**
 * Class VindiPlanItemSearchResult
 * @package Vindi\Payment\Model
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class VindiPlanItemSearchResult extends SearchResults implements VindiPlanItemSearchResultInterface
{
}
