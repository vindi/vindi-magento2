<?php
namespace Vindi\Payment\Controller\Subscription;

class Cron extends \Magento\Framework\App\Action\Action
{
    protected $connection;
    protected $resource;
    protected $syncSubscriptions;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Vindi\Payment\Cron\SyncSubscriptions $syncSubscriptions // Injetando a classe do cron job
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->syncSubscriptions = $syncSubscriptions; // Atribuindo a instância do cron job a uma propriedade
        parent::__construct($context);
    }

    public function execute()
    {
        $this->syncSubscriptions->execute();
        $stop = 1;

        echo "rodou";
    }
}
