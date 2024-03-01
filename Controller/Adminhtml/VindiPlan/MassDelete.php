<?php
namespace Vindi\Payment\Controller\Adminhtml\VindiPlan;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassDelete
 * @package Vindi\Payment\Controller\Adminhtml\Rule

 */
class MassDelete extends AbstractMassAction
{
    /**
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $items = $this->getCollection();
        $collectionSize = $items->getSize();

        foreach ($items as $item) {
            try {
                $item->delete();
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));

        return $this->getResultRedirect('*/*/');
    }
}
