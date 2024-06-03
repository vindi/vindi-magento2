<?php
declare(strict_types=1);

namespace Vindi\Payment\Ui\DataProvider\Log\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Vindi\Payment\Model\ResourceModel\Log\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    protected $loadedData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        foreach ($items as $log) {
            $this->loadedData[$log->getId()] = $log->getData();
        }

        return $this->loadedData;
    }
}
