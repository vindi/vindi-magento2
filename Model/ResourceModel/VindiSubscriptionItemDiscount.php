<?php

namespace Vindi\Payment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class VindiSubscriptionItemDiscount extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('vindi_subscription_item_discount', 'entity_id');
    }
}
