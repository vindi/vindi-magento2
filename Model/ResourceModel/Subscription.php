<?php

namespace Vindi\Payment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Subscription
 * @package Vindi\Payment\Model\ResourceModel
 */
class Subscription extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('vindi_subscription', 'id');
    }
}
