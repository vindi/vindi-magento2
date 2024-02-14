<?php
namespace Vindi\Payment\Controller\Adminhtml\VindiPlan;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassStatus
 * @package Vindi\Payment\Controller\Adminhtml\VindiPlan
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class MassStatus extends AbstractMassAction
{
    /**
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $items   = $this->getCollection();
        $status  = (int) $this->getRequest()->getParam('status');
        $updated = 0;

        foreach ($items as $item) {
            try {
                $item->setStatus($status);

                $data = [
                    'code'   => $item->getCode(),
                    'status' => $status,
                ];

                $item->save();

                $this->getPlan()->save($data);

                $updated++;
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been updated.', $updated));

        return $this->getResultRedirect('*/*/');
    }
}
