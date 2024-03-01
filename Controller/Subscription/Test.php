<?php
namespace Vindi\Payment\Controller\Subscription;

class Test extends \Magento\Framework\App\Action\Action
{
    protected $connection;
    protected $resource;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        parent::__construct($context);
    }

    public function execute()
    {
        $tableName = $this->resource->getTableName('vindi_subscription');
        $values = [];

        for ($i = 1; $i <= 200; $i++) {
            $date = date('Y-m-d H:i:s', strtotime("2023-01-01 +$i days"));
            $plan = 'Plano ' . ($i % 2 == 0 ? 'Basic' : 'Premium');
            $paymentMethod = $i % 2 == 0 ? 'vindi_bankslip' : 'vindi_pix';
            $values[] = [
                'client' => '2',
                'plan' => $plan,
                'start_at' => $date,
                'payment_method' => $paymentMethod,
                'payment_profile' => $i,
                'status' => 'active'
            ];
        }

        try {
            $this->connection->insertMultiple($tableName, $values);
            $this->messageManager->addSuccessMessage(__('200 registros inseridos com sucesso.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Erro ao inserir registros: ' . $e->getMessage()));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('vindi_vr/subscription/index'); // Redirecionar para onde você achar mais adequado
        return $resultRedirect;
    }
}
