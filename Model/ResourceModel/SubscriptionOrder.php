<?php
namespace Vindi\Payment\Model\ResourceModel;

use Vindi\Payment\Model\SubscriptionOrderFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class SubscriptionOrder
 * @package Vindi\Payment\Model\ResourceModel
 */
class SubscriptionOrder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var SubscriptionOrderFactory
     */
    protected $subscriptionorderFactory;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * SubscriptionOrder constructor.
     * @param Context $context
     * @param SubscriptionOrderFactory $subscriptionorderFactory
     * @param ReadFactory $readFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        SubscriptionOrderFactory $subscriptionorderFactory,
        ReadFactory $readFactory,
        Filesystem $filesystem
    ) {
        $this->subscriptionorderFactory = $subscriptionorderFactory;
        $this->readFactory = $readFactory;
        $this->filesystem = $filesystem;
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('vindi_subscription_orders', 'entity_id');
    }

    /**
     * @param $id
     * @return \Vindi\Payment\Model\SubscriptionOrder
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $subscriptionorder = $this->subscriptionorderFactory->create();
        $this->load($subscriptionorder, $id);

        if (!$subscriptionorder->getId()) {
            throw new NoSuchEntityException(__('SubscriptionOrder with id "%1" does not exist.', $id));
        }

        return $subscriptionorder;
    }
}
