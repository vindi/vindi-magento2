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

namespace Vindi\Payment\Api;

interface PaymentLinkRepositoryInterface
{
    /**
     * @param int $id
     * @return \Vindi\Payment\Api\Data\PaymentLinkInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * @param \Vindi\Payment\Api\Data\PaymentLinkInterface $paymentLink
     * @return \Vindi\Payment\Api\Data\PaymentLinkInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Vindi\Payment\Api\Data\PaymentLinkInterface $paymentLink);

    /**
     * @param \Vindi\Payment\Api\Data\PaymentLinkInterface $paymentLink
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Vindi\Payment\Api\Data\PaymentLinkInterface $paymentLink);
}
