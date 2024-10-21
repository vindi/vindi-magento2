<?php

namespace Vindi\Payment\Model\ResourceModel;

use Vindi\Payment\Model\SubscriptionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Subscription
 * @package Vindi\Payment\Model\ResourceModel
 */
class Subscription extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var SubscriptionFactory
     */
    protected $subscriptionFactory;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Subscription constructor.
     * @param Context $context
     * @param SubscriptionFactory $subscriptionFactory
     * @param ReadFactory $readFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        SubscriptionFactory $subscriptionFactory,
        ReadFactory $readFactory,
        Filesystem $filesystem
    ) {
        $this->subscriptionFactory = $subscriptionFactory;
        $this->readFactory = $readFactory;
        $this->filesystem = $filesystem;
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('vindi_subscription', 'id');
    }

    /**
     * Retrieve subscription by ID.
     *
     * @param int $id
     * @return \Vindi\Payment\Model\Subscription
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $subscription = $this->subscriptionFactory->create();
        $this->load($subscription, $id);

        if (!$subscription->getId()) {
            throw new NoSuchEntityException(__('Subscription with id "%1" does not exist.', $id));
        }

        return $subscription;
    }
}
