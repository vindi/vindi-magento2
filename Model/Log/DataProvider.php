<?php
declare(strict_types=1);

namespace Vindi\Payment\Model\Log;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Vindi\Payment\Api\LogRepositoryInterface;
use Vindi\Payment\Model\ResourceModel\Log\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var LogRepositoryInterface
     */
    protected $logRepository;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param LogRepositoryInterface $logRepository
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        LogRepositoryInterface $logRepository,
        array $meta = [],
        array $data = []
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->logRepository = $logRepository;
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        foreach ($items as $log) {
            $result['log_details']  = $log->getData();
            $result['entity_id'] = $log->getId();
            $this->loadedData[$log->getId()] = $result;
        }

        $data = $this->dataPersistor->get('vindi_payment_log');
        if (!empty($data)) {
            $log = $this->collection->getNewEmptyItem();
            $log->setData($data);
            $this->loadedData[$log->getId()] = $log->getData();
            $this->dataPersistor->clear('vindi_payment_log');
        }

        return $this->loadedData;
    }
}
