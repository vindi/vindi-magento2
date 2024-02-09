<?php
namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\VindiPlanItemInterface;
use Magento\Framework\Model\AbstractModel;

class VindiPlanItem extends AbstractModel implements VindiPlanItemInterface
{
    protected function _construct()
    {
        $this->_init(\Vindi\Payment\Model\ResourceModel\VindiPlanItem::class);
    }

    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function setId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getPlanId()
    {
        return $this->getData(self::PLAN_ID);
    }

    public function setPlanId($planId)
    {
        return $this->setData(self::PLAN_ID, $planId);
    }

    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    public function getCycles()
    {
        return $this->getData(self::CYCLES);
    }

    public function setCycles($cycles)
    {
        return $this->setData(self::CYCLES, $cycles);
    }
}
