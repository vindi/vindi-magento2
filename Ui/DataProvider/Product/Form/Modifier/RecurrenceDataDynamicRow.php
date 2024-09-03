<?php
namespace Vindi\Payment\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollection;
use Magento\Framework\Stdlib\ArrayManager;
use Vindi\Payment\Model\Config\Source\Options as PlanOptions;

/**
 * Data provider for the Recurrence Data dynamic row
 */
class RecurrenceDataDynamicRow extends AbstractModifier
{
    /**
     * Field name
     */
    public const VINDI_RECURRENCE_DATA = 'vindi_recurrence_data';

    /**
     * Data source default
     */
    private LocatorInterface $locator;

    /**
     * @var AttributeSetCollection
     */
    protected AttributeSetCollection $attributeSetCollection;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected \Magento\Framework\Serialize\SerializerInterface $serializer;

    /**
     * @var ArrayManager
     */
    protected ArrayManager $arrayManager;

    /**
     * @var PlanOptions
     */
    protected PlanOptions $planOptions;

    /**
     * @param LocatorInterface $locator
     * @param AttributeSetCollection $attributeSetCollection
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param ArrayManager $arrayManager
     * @param PlanOptions $planOptions
     */
    public function __construct(
        LocatorInterface $locator,
        AttributeSetCollection $attributeSetCollection,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        ArrayManager $arrayManager,
        PlanOptions $planOptions
    ) {
        $this->locator = $locator;
        $this->attributeSetCollection = $attributeSetCollection;
        $this->serializer = $serializer;
        $this->arrayManager = $arrayManager;
        $this->planOptions = $planOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $fieldCode = self::VINDI_RECURRENCE_DATA;

        $model = $this->locator->getProduct();
        $modelId = $model->getId();

        $recurrenceData = $model->getData($fieldCode);

        if ($recurrenceData) {
            $recurrenceData = $this->serializer->unserialize($recurrenceData);
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/' . $fieldCode;
            $data = $this->arrayManager->set($path, $data, $recurrenceData);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $recurrencePath = $this->arrayManager->findPath(self::VINDI_RECURRENCE_DATA, $meta, null, 'children');

        if ($recurrencePath) {
            $meta = $this->arrayManager->merge(
                $recurrencePath,
                $meta,
                $this->getRecurrenceDataFieldStructure($meta, $recurrencePath)
            );
        }

        return $meta;
    }

    /**
     * Get structure of the Recurrence Data dynamic row
     *
     * @param array $meta
     * @param string $recurrencePath
     * @return array
     */
    protected function getRecurrenceDataFieldStructure($meta, $recurrencePath)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => DynamicRows::NAME,
                        'label' => __('Recurrence Data'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' => $this->arrayManager->get($recurrencePath . '/arguments/data/config/sortOrder', $meta),
                    ],
                ],
            ],
            'children' => [
                'record' => $this->getRecordStructure(),
            ],
        ];
    }

    /**
     * Get structure of the Recurrence Data dynamic row record
     *
     * @return array
     */
    protected function getRecordStructure()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'isTemplate' => true,
                        'is_collection' => true,
                        'component' => 'Magento_Ui/js/dynamic-rows/record',
                        'dataScope' => '',
                    ],
                ],
            ],
            'children' => [
                'plan' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Field::NAME,
                                'formElement' => Select::NAME,
                                'dataType' => Text::NAME,
                                'label' => __('Plan'),
                                'options' => $this->planOptions->toOptionArray(),
                                'dataScope' => 'plan',
                                'sortOrder' => 10,
                                'validation' => [
                                    'required-entry' => true,
                                ],
                            ],
                        ],
                    ],
                ],
                'price' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Field::NAME,
                                'formElement' => Input::NAME,
                                'dataType' => Text::NAME,
                                'label' => __('Price'),
                                'dataScope' => 'price',
                                'sortOrder' => 20,
                                'validation' => [
                                    'required-entry' => true,
                                    'validate-greater-than-zero' => true,
                                ],
                            ],
                        ],
                    ],
                ],
                'actionDelete' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'actionDelete',
                                'dataType' => Text::NAME,
                                'label' => __('Delete'),
                                'sortOrder' => 30,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
