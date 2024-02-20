<?php
namespace Vindi\Payment\Model\VindiPlan;

use Vindi\Payment\Helper\Data;
use Vindi\Payment\Model\ResourceModel\VindiPlan\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class DataProvider
 * @package Vindi\Payment\Model\VindiPlan
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class DataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider
{
    /**
     * @var \Vindi\Payment\Model\ResourceModel\VindiPlan\Collection
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
     * @param CollectionFactory $vindiplanCollectionFactory
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
        CollectionFactory $vindiplanCollectionFactory,
        DataPersistorInterface $dataPersistor,
        Data $helper,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->collection = $vindiplanCollectionFactory->create();
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

        /** @var \Vindi\Payment\Model\VindiPlan $vindiplan */
        foreach ($items as $vindiplan) {
            $result['settings']  = $vindiplan->getData();
            $result['entity_id'] = $vindiplan->getId();

            $this->loadedData[$vindiplan->getId()] = $result;
        }

        $data = $this->dataPersistor->get('cedran_vindiplan');
        if (!empty($data)) {
            $block = $this->collection->getNewEmptyItem();
            $block->setData($data);
            $this->loadedData[$vindiplan->getId()] = $vindiplan->getData();
            $this->dataPersistor->clear('cedran_vindiplan');
        }

        return $this->loadedData;
    }
}
