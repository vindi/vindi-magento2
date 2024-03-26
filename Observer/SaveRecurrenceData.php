<?php
namespace Vindi\Payment\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class SaveRecurrenceData
 * @package Vindi\Payment\Observer
 */
class SaveRecurrenceData implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * SaveRecurrenceData constructor.
     * @param RequestInterface $request
     * @param SerializerInterface $serializer
     */
    public function __construct(
        RequestInterface $request,
        SerializerInterface $serializer
    ) {
        $this->request = $request;
        $this->serializer = $serializer;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $wholeRequest = $this->request->getPostValue();

        if (!empty($wholeRequest['product'][\Vindi\Payment\Ui\DataProvider\Product\Form\Modifier\RecurrenceDataDynamicRow::VINDI_RECURRENCE_DATA])) {
            $recurrenceData = $wholeRequest['product'][\Vindi\Payment\Ui\DataProvider\Product\Form\Modifier\RecurrenceDataDynamicRow::VINDI_RECURRENCE_DATA];

            if (is_array($recurrenceData)) {
                $recurrenceData = $this->serializer->serialize($recurrenceData);
                $product->setData(\Vindi\Payment\Ui\DataProvider\Product\Form\Modifier\RecurrenceDataDynamicRow::VINDI_RECURRENCE_DATA, $recurrenceData);
            }
        }

        if (isset($wholeRequest['product']['vindi_enable_recurrence'])) {
            $product->setData('vindi_enable_recurrence', $wholeRequest['product']['vindi_enable_recurrence']);
        }
    }
}
