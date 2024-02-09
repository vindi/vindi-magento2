<?php
namespace Vindi\Payment\Controller\Adminhtml\VindiPlan;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Vindi\Payment\Model\ResourceModel\VindiPlan\CollectionFactory;

/**
 * Class AbstractMassAction
 * @package Biz\Gifts\Controller\Adminhtml\Rule
 */
abstract class AbstractMassAction extends Action
{
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * AbstractMassAction constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->_filter = $filter;
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context);
    }

    /**
     * @return AbstractDb
     * @throws LocalizedException
     */
    public function getCollection()
    {
        return $this->_filter->getCollection($this->collectionFactory->create());
    }

    /**
     * @param string $path
     *
     * @return ResultRedirect
     */
    protected function getResultRedirect($path)
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath($path);
    }
}
