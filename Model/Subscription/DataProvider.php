<?php

namespace Vindi\Payment\Model\Subscription;

use Vindi\Payment\Helper\Data;
use Vindi\Payment\Model\ResourceModel\Subscription\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class DataProvider
 * @package Vindi\Payment\Model\Subscription
 */
class DataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider
{
    /**
     * @var \Vindi\Payment\Model\ResourceModel\Subscription\Collection
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
     * @param CollectionFactory $subscriptionCollectionFactory
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
        CollectionFactory $subscriptionCollectionFactory,
        DataPersistorInterface $dataPersistor,
        Data $helper,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->collection = $subscriptionCollectionFactory->create();
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

        /** @var \Vindi\Payment\Model\Subscription $subscription */
        foreach ($items as $subscription) {
            $result['settings'] = $subscription->getData();
            $result['id'] = $subscription->getId();

            $result['vindi_subscription_items_grid']['payment_method'] = $subscription->getPaymentMethod();

            $this->loadedData[$subscription->getId()] = $result;
        }

        $data = $this->dataPersistor->get('vindi_payment_subscription');
        if (!empty($data)) {
            $subscription = $this->collection->getNewEmptyItem();
            $subscription->setData($data);
            $this->loadedData[$subscription->getId()] = $subscription->getData();
            $this->dataPersistor->clear('vindi_payment_subscription');
        }

        return $this->loadedData;
    }
}
