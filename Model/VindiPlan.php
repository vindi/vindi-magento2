<?php
namespace Vindi\Payment\Model;

use Vindi\Payment\Api\Data\VindiPlanInterface;

/**
 * Class VindiPlan
 * @package Vindi\Payment\Model
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class VindiPlan extends \Magento\Framework\Model\AbstractModel implements VindiPlanInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vindi\Payment\Model\ResourceModel\VindiPlan');
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array|int|mixed|null
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @param $entityId
     * @return VindiPlan|void
     */
    public function setId($entityId)
    {
        $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @param $name
     * @return VindiPlan|void
     */
    public function setName($name)
    {
        $this->setData(self::NAME, $name);
    }

    /**
     * @return array|int|mixed|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param $status
     * @return VindiPlan|void
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param $createAt
     * @return VindiPlan|void
     */
    public function setCreatedAt($createAt)
    {
        $this->setData(self::CREATED_AT, $createAt);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param $updatedAt
     * @return VindiPlan|void
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
