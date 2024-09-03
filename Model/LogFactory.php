<?php
declare(strict_types=1);

namespace Vindi\Payment\Model;

use Magento\Framework\ObjectManagerInterface;

class LogFactory
{
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(array $data = [])
    {
        return $this->objectManager->create(Log::class, $data);
    }
}
