<?php
namespace Vindi\Payment\Model\ResourceModel;

/**
 * Class VindiSubscriptionItem
 * @package Vindi\Payment\Model\ResourceModel
 */
class VindiSubscriptionItem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('vindi_subscription_item', 'entity_id');
    }
}
