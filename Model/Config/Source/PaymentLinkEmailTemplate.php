<?php
/**
 *
 *
 *
 *
 *
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Vindi
 * @package     Vindi_Payment
 *
 *
 */

namespace Vindi\Payment\Model\Config\Source;


use Magento\Email\Model\Template\Config;
class PaymentLinkEmailTemplate implements \Magento\Framework\Data\OptionSourceInterface
{

    private $emailTemplateConfig;

    public function __construct(Config $emailTemplateConfig)
    {
        $this->emailTemplateConfig = $emailTemplateConfig;
    }


    public function toOptionArray()
    {
        return $this->emailTemplateConfig->getAvailableTemplates();
    }
}
