<?php
namespace Vindi\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Vindi\Payment\Model\ResourceModel\PaymentProfile\CollectionFactory as PaymentProfileCollectionFactory;

/**
 * Class PaymentProfiles
 * @package Vindi\Payment\Model\Config\Source
 */
class PaymentProfiles implements OptionSourceInterface
{
    /**
     * @var PaymentProfileCollectionFactory
     */
    protected $paymentProfileCollectionFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param PaymentProfileCollectionFactory $paymentProfileCollectionFactory
     * @param Registry $registry
     */
    public function __construct(
        PaymentProfileCollectionFactory $paymentProfileCollectionFactory,
        Registry $registry
    ) {
        $this->paymentProfileCollectionFactory = $paymentProfileCollectionFactory;
        $this->registry = $registry;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     * @throws LocalizedException
     */
    public function toOptionArray()
    {
        $options = [];

        $customerId             = $this->registry->registry('current_customer_id');
        $paymentMethod          = $this->registry->registry('vindi_current_subscription_payment_method');
        $selectedPaymentProfile = $this->registry->registry('vindi_current_subscription_payment_profile'); // Retrieve selected payment profile

        if (!$customerId) {
            throw new LocalizedException(__('Customer ID is not set.'));
        }

        if ($paymentMethod !== 'credit_card') {
            $options[] = ['value' => '', 'label' => __('--- ---')];
            return $options;
        }

        $paymentProfileCollection = $this->paymentProfileCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->setOrder('created_at', 'DESC');

        $paymentProfileOptions = [];

        if ($paymentProfileCollection->getSize()) {
            foreach ($paymentProfileCollection as $profile) {
                if ($profile->getId() && $profile->getCcLast4()) {
                    $paymentProfileOptions[] = [
                        'value' => $profile->getPaymentProfileId(),
                        'label' => __('%2 **** %3 (%1)', $profile->getCcName(), $profile->getCcType(), $profile->getCcLast4())
                    ];
                }
            }
        } else {
            $paymentProfileOptions[] = ['value' => '', 'label' => __('No Payment Profiles Available')];
        }

        if ($selectedPaymentProfile) {
            $options = $this->moveSelectedProfileToFirstPosition($paymentProfileOptions, $selectedPaymentProfile);
        } else {
            $options = $paymentProfileOptions;
        }

        return $options;
    }

    /**
     * Move the selected profile to the first position in the options array
     *
     * @param array $options
     * @param int|null $selectedPaymentProfile
     * @return array
     */
    protected function moveSelectedProfileToFirstPosition(array $options, $selectedPaymentProfile)
    {
        foreach ($options as $key => $option) {
            if ($option['value'] == $selectedPaymentProfile) {
                unset($options[$key]);
                array_unshift($options, [
                    'value' => $selectedPaymentProfile,
                    'label' => $option['label'],
                    'selected' => true
                ]);
                break;
            }
        }
        return $options;
    }
}

