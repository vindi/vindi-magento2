<?php

namespace Vindi\Payment\Api;

use Vindi\Payment\Api\Data\PaymentProfileInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface PaymentProfileRepositoryInterface
{
    public function getById($entityId);

    public function getByProfileId($profileId);

    public function save(PaymentProfileInterface $paymentProfile);

    public function delete(PaymentProfileInterface $paymentProfile);

    public function getList(SearchCriteriaInterface $criteria);
}
