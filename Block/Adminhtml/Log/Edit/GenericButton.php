<?php
namespace Vindi\Payment\Block\Adminhtml\Log\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Vindi\Payment\Api\LogRepositoryInterface;

class GenericButton
{
    protected $context;
    protected $logRepository;

    public function __construct(
        Context $context,
        LogRepositoryInterface $logRepository
    ) {
        $this->context = $context;
        $this->logRepository = $logRepository;
    }

    public function getLogId()
    {
        try {
            return $this->logRepository->getById(
                $this->context->getRequest()->getParam('entity_id')
            )->getId();
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }

    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
