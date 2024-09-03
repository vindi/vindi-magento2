<?php
declare(strict_types=1);

namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\LogInterface;
use Vindi\Payment\Api\LogRepositoryInterface;
use Vindi\Payment\Model\ResourceModel\Log as LogResourceModel;
use Vindi\Payment\Model\ResourceModel\Log\CollectionFactory as LogCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class LogRepository implements LogRepositoryInterface
{
    /**
     * @var LogResourceModel
     */
    protected $resource;

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @var LogCollectionFactory
     */
    protected $logCollectionFactory;

    /**
     * LogRepository constructor.
     *
     * @param LogResourceModel $resource
     * @param LogFactory $logFactory
     * @param LogCollectionFactory $logCollectionFactory
     */
    public function __construct(
        LogResourceModel $resource,
        LogFactory $logFactory,
        LogCollectionFactory $logCollectionFactory
    ) {
        $this->resource = $resource;
        $this->logFactory = $logFactory;
        $this->logCollectionFactory = $logCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(LogInterface $log): LogInterface
    {
        $this->resource->save($log);
        return $log;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id): LogInterface
    {
        $log = $this->logFactory->create();
        $this->resource->load($log, $id);
        if (!$log->getId()) {
            throw new NoSuchEntityException(__('Log with id "%1" does not exist.', $id));
        }
        return $log;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(LogInterface $log): bool
    {
        return $this->resource->delete($log);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id): bool
    {
        $log = $this->getById($id);
        return $this->delete($log);
    }
}
