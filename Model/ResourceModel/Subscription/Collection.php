<?php
namespace Vindi\Payment\Model\ResourceModel\Subscription;

/**
 * Class Collection
 * @package Vindi\Payment\Model\ResourceModel\Subscription
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'vindi_payment_subscriptions_collection';
    protected $_eventObject = 'subscription_collection';

    /**
     * @var array|null
     */
    protected $_options;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vindi\Payment\Model\Subscription', 'Vindi\Payment\Model\ResourceModel\Subscription');
    }
}
