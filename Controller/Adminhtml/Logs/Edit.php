<?php
declare(strict_types=1);

namespace Vindi\Payment\Controller\Adminhtml\Logs;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Vindi\Payment\Api\LogRepositoryInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

class Edit extends Action
{
    protected $resultPageFactory;
    protected $logRepository;
    protected $coreRegistry;
    protected $dataPersistor;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        LogRepositoryInterface $logRepository,
        Registry $coreRegistry,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->logRepository = $logRepository;
        $this->coreRegistry = $coreRegistry;
        $this->dataPersistor = $dataPersistor;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->logRepository->getById($id);

        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('This log no longer exists.'));
            return $this->_redirect('*/*/');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Log Information'));

        return $resultPage;
    }
}
