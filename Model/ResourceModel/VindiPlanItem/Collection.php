<?php
namespace Vindi\Payment\Model\ResourceModel\VindiPlanItem;

/**
 * Class Collection
 * @package Vindi\Payment\Model\ResourceModel\VindiPlanItem
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'vindi_payment_vindiplanitems_collection';
    protected $_eventObject = 'vindiplanitem_collection';

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
        $this->_init('Vindi\Payment\Model\VindiPlanItem', 'Vindi\Payment\Model\ResourceModel\VindiPlanItem');
    }

    public function toOptionArray()
    {
        $collection = $this->getItems();
        $this->_options = [['label' => '', 'value' => '']];

        foreach ($collection as $vindiplanitem) {
            $this->_options[] = [
                'label' => __($vindiplanitem->getName()),
                'value' => $vindiplanitem->getId()
            ];
        }

        return $this->_options;
    }
}
