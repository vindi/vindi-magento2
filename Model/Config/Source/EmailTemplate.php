<?php
namespace Vindi\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory as TemplateCollectionFactory;

class EmailTemplate implements OptionSourceInterface
{
    /**
     * @var TemplateCollectionFactory
     */
    private $templateCollectionFactory;

    /**
     * EmailTemplate constructor.
     * @param TemplateCollectionFactory $templateCollectionFactory
     */
    public function __construct(
        TemplateCollectionFactory $templateCollectionFactory
    ) {
        $this->templateCollectionFactory = $templateCollectionFactory;
    }

    /**
     * Get available email templates including custom templates based on 'vindi_vr_payment_link_template'
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        $collection = $this->templateCollectionFactory->create();
        $collection->load();

        $options[] = [
            'value' => 'vindi_vr_payment_link_template',
            'label' => __('Payment Link Notification (Default)'),
        ];

        foreach ($collection as $template) {
            if ($template->getOrigTemplateCode() == 'vindi_vr_payment_link_template') {
                $options[] = [
                    'value' => $template->getTemplateId(),
                    'label' => $template->getTemplateCode(),
                ];
            }
        }

        return $options;
    }
}
