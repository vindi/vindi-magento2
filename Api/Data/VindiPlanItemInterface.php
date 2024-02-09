<?php
declare(strict_types=1);

namespace Vindi\Payment\Api\Data;

/**
 * Interface VindiPlanItemInterface
 * @package Vindi\Payment\Api\Data
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
interface VindiPlanItemInterface
{
    const ENTITY_ID   = 'entity_id';
    const PLAN_ID     = 'plan_id';
    const PRODUCT_ID  = 'product_id';
    const CYCLES      = 'cycles';

    public function getId();
    public function setId($entityId);
    public function getPlanId();
    public function setPlanId($planId);
    public function getProductId();
    public function setProductId($productId);
    public function getCycles();
    public function setCycles($cycles);
}
