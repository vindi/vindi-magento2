<?php
declare(strict_types=1);

namespace Vindi\Payment\Api;

use Vindi\Payment\Api\Data\LogInterface;

interface LogRepositoryInterface
{
    /**
     * Save log
     *
     * @param LogInterface $log
     * @return LogInterface
     */
    public function save(LogInterface $log): LogInterface;

    /**
     * Get log by ID
     *
     * @param int $id
     * @return LogInterface
     */
    public function getById($id): LogInterface;

    /**
     * Delete log
     *
     * @param LogInterface $log
     * @return bool
     */
    public function delete(LogInterface $log): bool;

    /**
     * Delete log by ID
     *
     * @param int $id
     * @return bool
     */
    public function deleteById($id): bool;
}
