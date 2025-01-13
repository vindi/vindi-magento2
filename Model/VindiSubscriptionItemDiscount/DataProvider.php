<?php

namespace Vindi\Payment\Model\VindiSubscriptionItemDiscount;

use Vindi\Payment\Helper\Data;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider
{
    protected $collection;
    protected $dataPersistor;
    protected $loadedData;
    protected $helper;

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $subscriptionItemCollectionFactory,
        DataPersistorInterface $dataPersistor,
        Data $helper,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->collection = $subscriptionItemCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->helper = $helper;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();

        foreach ($items as $subscriptionItem) {
            $result['settings']  = $subscriptionItem->getData();
            $result['entity_id'] = $subscriptionItem->getId();

            $result['settings']['price'] = number_format((float) $subscriptionItem->getPrice(), 2, '.', '');

            $this->loadedData[$subscriptionItem->getEntityId()] = $result;
        }

        $data = $this->dataPersistor->get('vindi_subscription_item_discount');
        if (!empty($data)) {
            $subscriptionItem = $this->collection->getNewEmptyItem();
            $subscriptionItem->setData($data);
            $this->loadedData[$subscriptionItem->getEntityId()] = $subscriptionItem->getData();
            $this->dataPersistor->clear('vindi_subscription_item_discount');
        }

        return $this->loadedData;
    }
}
