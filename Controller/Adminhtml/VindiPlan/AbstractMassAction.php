<?php
namespace Vindi\Payment\Controller\Adminhtml\VindiPlan;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Vindi\Payment\Model\ResourceModel\VindiPlan\CollectionFactory;
use Vindi\Payment\Model\Vindi\Plan;

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
     * @var Plan
     */
    protected $plan;

    /**
     * AbstractMassAction constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Plan $plan
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Plan $plan,
    ) {
        $this->_filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->plan = $plan;

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

    /**
     * @return Plan
     */
    protected function getPlan()
    {
        return $this->plan;
    }
}
