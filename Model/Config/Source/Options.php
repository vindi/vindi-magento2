<?php
namespace Vindi\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Vindi\Payment\Model\ResourceModel\VindiPlan\CollectionFactory as VindiPlanCollectionFactory;

/**
 * Class Options
 * @package Vindi\Payment\Model\Config\Source
 */
class Options implements OptionSourceInterface
{
    /**
     * @var VindiPlanCollectionFactory
     */
    protected $collectionFactory;

    /**
     * Options constructor.
     * @param VindiPlanCollectionFactory $collectionFactory
     */
    public function __construct(VindiPlanCollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->collectionFactory->create();
        $options = [];
        foreach ($collection as $item) {
            $options[] = ['value' => $item->getEntityId(), 'label' => $item->getName()];
        }
        return $options;
    }
}
