<?php
namespace Vindi\Payment\Model\Config\Source;

use Magento\Email\Model\Template\Config;

/**
 *
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
class EmailTemplate implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var Config
     */
    private $emailTemplateConfig;

    /**
     * EmailTemplate constructor.
     * @param Config $emailTemplateConfig
     */
    public function __construct(Config $emailTemplateConfig)
    {
        $this->emailTemplateConfig = $emailTemplateConfig;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->emailTemplateConfig->getAvailableTemplates();
    }
}
