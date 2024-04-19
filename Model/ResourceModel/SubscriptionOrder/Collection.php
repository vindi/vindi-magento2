<?php
namespace Vindi\Payment\Model\ResourceModel\SubscriptionOrder;

/**
 * Class Collection
 * @package Vindi\Payment\Model\ResourceModel\SubscriptionOrder
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'vindi_payment_subscriptionorders_collection';
    protected $_eventObject = 'subscriptionorder_collection';

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
        $this->_init('Vindi\Payment\Model\SubscriptionOrder', 'Vindi\Payment\Model\ResourceModel\SubscriptionOrder');
    }

    public function toOptionArray()
    {
        $collection = $this->getItems();
        $this->_options = [['label' => '', 'value' => '']];

        foreach ($collection as $subscriptionorder) {
            $this->_options[] = [
                'label' => __($subscriptionorder->getName()),
                'value' => $subscriptionorder->getId()
            ];
        }

        return $this->_options;
    }
}
