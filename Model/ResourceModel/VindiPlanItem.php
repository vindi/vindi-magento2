<?php
namespace Vindi\Payment\Model\ResourceModel;

use Vindi\Payment\Model\VindiPlanItemFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class VindiPlanItem
 * @package Vindi\Payment\Model\ResourceModel
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class VindiPlanItem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var VindiPlanItemFactory
     */
    protected $vindiplanitemFactory;

    /**
     * VindiPlanItem constructor.
     * @param Context $context
     * @param VindiPlanItemFactory $vindiplanitemFactory
     * @param ReadFactory $readFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        VindiPlanItemFactory $vindiplanitemFactory,
        ReadFactory $readFactory,
        Filesystem $filesystem
    ) {
        $this->vindiplanitemFactory = $vindiplanitemFactory;
        $this->readFactory = $readFactory;
        $this->filesystem = $filesystem;
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('vindi_plan_items', 'entity_id');
    }

    /**
     * @param $id
     * @return \Vindi\Payment\Model\VindiPlanItem
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $vindiplanitem = $this->vindiplanitemFactory->create();
        $this->load($vindiplanitem, $id);

        if (!$vindiplanitem->getId()) {
            throw new NoSuchEntityException(__('VindiPlanItem with id "%1" does not exist.', $id));
        }

        return $vindiplanitem;
    }
}
