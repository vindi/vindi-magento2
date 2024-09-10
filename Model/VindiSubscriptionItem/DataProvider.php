<?php

namespace Vindi\Payment\Model\VindiSubscriptionItem;

use Vindi\Payment\Helper\Data;
use Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider
{
    /**
     * @var \Vindi\Payment\Model\ResourceModel\VindiSubscriptionItem\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $subscriptionItemCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param Data $helper
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     */
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

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();

        /** @var \Vindi\Payment\Model\VindiSubscriptionItem $subscriptionItem */
        foreach ($items as $subscriptionItem) {
            $result['settings']  = $subscriptionItem->getData();
            $result['entity_id'] = $subscriptionItem->getEntityId();

            $this->loadedData[$subscriptionItem->getEntityId()] = $result;
        }

        $data = $this->dataPersistor->get('vindi_payment_subscription_item');
        if (!empty($data)) {
            $subscriptionItem = $this->collection->getNewEmptyItem();
            $subscriptionItem->setData($data);
            $this->loadedData[$subscriptionItem->getEntityId()] = $subscriptionItem->getData();
            $this->dataPersistor->clear('vindi_payment_subscription_item');
        }

        return $this->loadedData;
    }
}
