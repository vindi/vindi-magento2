<?php
namespace Vindi\Payment\Model\ResourceModel;

use Vindi\Payment\Model\VindiPlanFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class VindiPlan
 * @package Vindi\Payment\Model\ResourceModel

 */
class VindiPlan extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var VindiPlanFactory
     */
    protected $vindiplanFactory;

    /**
     * VindiPlan constructor.
     * @param Context $context
     * @param VindiPlanFactory $vindiplanFactory
     * @param ReadFactory $readFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        VindiPlanFactory $vindiplanFactory,
        ReadFactory $readFactory,
        Filesystem $filesystem
    ) {
        $this->vindiplanFactory = $vindiplanFactory;
        $this->readFactory = $readFactory;
        $this->filesystem = $filesystem;
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('vindi_plans', 'entity_id');
    }

    /**
     * @param $id
     * @return \Vindi\Payment\Model\VindiPlan
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $vindiplan = $this->vindiplanFactory->create();
        $this->load($vindiplan, $id);

        if (!$vindiplan->getId()) {
            throw new NoSuchEntityException(__('VindiPlan with id "%1" does not exist.', $id));
        }

        return $vindiplan;
    }
}
