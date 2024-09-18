<?php

namespace Vindi\Payment\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Vindi\Payment\Model\PaymentLinkService;

class PaymentLink extends Column
{
    /**
     * @var PaymentLinkService
     */
    protected $paymentLinkService;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PaymentLinkService $paymentLinkService
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PaymentLinkService $paymentLinkService,
        array $components = [],
        array $data = []
    ) {
        $this->paymentLinkService = $paymentLinkService;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare the data for the custom column
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $orderId = $item['entity_id'];
                $paymentLink = $this->paymentLinkService->getPaymentLinkByOrderId($orderId);

                if ($paymentLink) {
                    $link = $paymentLink->getLink();
                    $item[$this->getData('name')] = __('Link generated');
                } else {
                    $item[$this->getData('name')] = '';
                }
            }
        }

        return $dataSource;
    }
}
