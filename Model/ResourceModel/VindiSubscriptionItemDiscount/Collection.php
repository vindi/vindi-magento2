<?php

namespace Vindi\Payment\Model\ResourceModel\VindiSubscriptionItemDiscount;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vindi\Payment\Model\VindiSubscriptionItemDiscount as Model;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItemDiscount as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
