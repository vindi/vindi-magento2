<?php
namespace Vindi\Payment\Model\ResourceModel\VindiPlan;

/**
 * Class Collection
 * @package Vindi\Payment\Model\ResourceModel\VindiPlan

 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'vindi_payment_vindiplans_collection';
    protected $_eventObject = 'vindiplan_collection';

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
        $this->_init('Vindi\Payment\Model\VindiPlan', 'Vindi\Payment\Model\ResourceModel\VindiPlan');
    }

    public function toOptionArray()
    {
        $collection = $this->getItems();
        $this->_options = [['label' => '', 'value' => '']];

        foreach ($collection as $vindiplan) {
            $this->_options[] = [
                'label' => __($vindiplan->getName()),
                'value' => $vindiplan->getId()
            ];
        }

        return $this->_options;
    }
}
