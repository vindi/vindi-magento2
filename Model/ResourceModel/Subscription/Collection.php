<?php

namespace Vindi\Payment\Model\ResourceModel\Subscription;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vindi\Payment\Model\Subscription;

/**
 * Class Collection
 * @package Vindi\Payment\Model\ResourceModel\Subscription
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(
            Subscription::class,
            \Vindi\Payment\Model\ResourceModel\Subscription::class
        );
    }
}
