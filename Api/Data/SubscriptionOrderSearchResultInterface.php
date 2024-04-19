<?php
declare(strict_types=1);

namespace Vindi\Payment\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface SubscriptionOrderSearchResultInterface
 * @package Vindi\Payment\Api\Data
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
interface SubscriptionOrderSearchResultInterface extends SearchResultsInterface
{
    /**
     * @return SubscriptionOrderInterface[]
     */
    public function getItems();

    /**
     * @param SubscriptionOrderInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
