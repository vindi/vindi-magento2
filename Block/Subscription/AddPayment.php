<?php

namespace Vindi\Payment\Block\Subscription;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Data\Form\FormKey;

/**
 * Class AddPayment
 *
 * @package Vindi\Payment\Block\Subscription
 */
class AddPayment extends Template
{
    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * AddPayment constructor.
     *
     * @param Template\Context $context
     * @param FormKey $formKey
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        FormKey $formKey,
        array $data = []
    ) {
        $this->formKey = $formKey;
        parent::__construct($context, $data);
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('vindi_vr/subscription/savepayment');
    }

    /**
     * Retrieve form key for CSRF validation
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
