<?php
namespace Vindi\Payment\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;

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
     * @var ManagerInterface
     */
    protected ManagerInterface $messageManager;

    /**
     * SaveRecurrenceData constructor.
     * @param RequestInterface $request
     * @param SerializerInterface $serializer
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        RequestInterface $request,
        SerializerInterface $serializer,
        ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->serializer = $serializer;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $wholeRequest = $this->request->getPostValue();

        $recurrenceDataKey = \Vindi\Payment\Ui\DataProvider\Product\Form\Modifier\RecurrenceDataDynamicRow::VINDI_RECURRENCE_DATA;

        if (isset($wholeRequest['product']['vindi_enable_recurrence']) && $wholeRequest['product']['vindi_enable_recurrence'] === '1') {
            if (isset($wholeRequest['product'][$recurrenceDataKey])) {
                $recurrenceData = $wholeRequest['product'][$recurrenceDataKey];

                if (is_array($recurrenceData)) {
                    foreach ($recurrenceData as $plan) {
                        if (empty($plan['plan']) || empty($plan['price'])) {
                            throw new LocalizedException(
                                __('Recurrence is enabled but one or more plans are missing or have no price.')
                            );
                        }
                    }
                    $recurrenceData = $this->serializer->serialize($recurrenceData);
                    $product->setData($recurrenceDataKey, $recurrenceData);
                } else {
                    throw new LocalizedException(
                        __('Recurrence is enabled but no plans are defined.')
                    );
                }
            } else {
                throw new LocalizedException(
                    __('Recurrence is enabled but no plans are defined.')
                );
            }
        } else {
            $product->setData($recurrenceDataKey, null);
        }

        if (isset($wholeRequest['product']['vindi_enable_recurrence'])) {
            $product->setData('vindi_enable_recurrence', $wholeRequest['product']['vindi_enable_recurrence']);
        }
    }
}
