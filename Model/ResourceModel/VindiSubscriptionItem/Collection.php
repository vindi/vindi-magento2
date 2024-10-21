<?php
namespace Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vindi\Payment\Model\VindiSubscriptionItem;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem as VindiSubscriptionItemResource;

/**
 * Class Collection
 * @package Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(VindiSubscriptionItem::class, VindiSubscriptionItemResource::class);
    }
}
