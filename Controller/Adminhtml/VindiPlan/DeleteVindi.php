<?php
namespace Vindi\Payment\Controller\Adminhtml\VindiPlan;

use Vindi\Payment\Model\VindiPlanFactory;
use Vindi\Payment\Model\VindiPlanRepository;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;

/**
 * Class DeleteVindi
 * @package Vindi\Payment\Controller\Adminhtml\VindiPlan
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class DeleteVindi extends Action
{
    /**
     * @var VindiPlanFactory
     */
    protected $vindiplanFactory;

    /**
     * @var VindiPlanRepository
     */
    protected $vindiplanRepository;

    /**
     * @param Context $context
     * @param VindiPlanFactory $vindiplanFactory
     * @param VindiPlanRepository $vindiplanRepository
     */
    public function __construct(
        Context $context,
        VindiPlanFactory $vindiplanFactory,
        VindiPlanRepository $vindiplanRepository
    ) {
        parent::__construct($context);
        $this->vindiplanFactory = $vindiplanFactory;
        $this->vindiplanRepository = $vindiplanRepository;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');

        try {
            $vindiplan = $this->vindiplanRepository->getById($id);
            $this->vindiplanRepository->delete($vindiplan);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $this->messageManager->addSuccessMessage(__('Item deleted succesfully!'));
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
