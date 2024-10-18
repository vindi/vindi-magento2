<?php

declare(strict_types=1);

/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Vindi
 * @package     Vindi_Payment
 */

namespace Vindi\Payment\Model\ResourceModel;

use Vindi\Payment\Model\PaymentLinkFactory;
use Vindi\Payment\Api\PaymentLinkRepositoryInterface;
use Vindi\Payment\Model\ResourceModel\PaymentLink as ResourcePaymentLink;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class PaymentLinkRepository implements PaymentLinkRepositoryInterface
{
    /**
     * @var ResourcePaymentLink
     */
    protected $resource;

    /**
     * @var \Vindi\Payment\Model\PaymentLinkFactory
     */
    protected $paymentLinkFactory;

    /**
     * @param ResourcePaymentLink $resource
     * @param \Vindi\Payment\Model\PaymentLinkFactory $paymentLinkFactory
     */
    public function __construct(
        ResourcePaymentLink $resource,
        \Vindi\Payment\Model\PaymentLinkFactory $paymentLinkFactory
    ) {
        $this->resource = $resource;
        $this->paymentLinkFactory = $paymentLinkFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        /** @var \Vindi\Payment\Model\PaymentLink $paymentLink */
        $paymentLink = $this->paymentLinkFactory->create();
        $this->resource->load($paymentLink, $id);
        if (!$paymentLink->getId()) {
            throw new NoSuchEntityException(__('Item with id "%1" does not exist.', $id));
        }
        return $paymentLink;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Vindi\Payment\Api\Data\PaymentLinkInterface $paymentLink)
    {
        try {
            $this->resource->save($paymentLink);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the paymentLink info: %1',
                $exception->getMessage()
            ));
        }
        return $paymentLink;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Vindi\Payment\Api\Data\PaymentLinkInterface $paymentLink)
    {
        try {
            $this->resource->delete($paymentLink);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the entry: %1', $exception->getMessage())
            );
        }
        return true;
    }
}
