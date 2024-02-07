<?php
declare(strict_types=1);

namespace Vindi\Payment\Api\Data;

/**
 * Interface VindiPlanInterface
 * @package Vindi\Payment\Api\Data
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
interface VindiPlanInterface
{
    /**
     * Constants for keys of data array.
     */
    const ENTITY_ID      = 'entity_id';
    const NAME           = 'name';
    const STATUS         = 'status';
    const CREATED_AT     = 'created_at';
    const UPDATED_AT     = 'updated_at';

    /**
     * Get entity_id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set $entityId
     *
     * @param int $entityId
     * @return $this
     */
    public function setId($entityId);

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set $name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set $status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get created_at
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set $createdAt
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set $updatedAt
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}
