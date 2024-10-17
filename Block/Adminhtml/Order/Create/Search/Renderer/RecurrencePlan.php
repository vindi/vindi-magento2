<?php
namespace Vindi\Payment\Block\Adminhtml\Order\Create\Search\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Vindi\Payment\Api\VindiPlanRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;

class RecurrencePlan extends AbstractRenderer
{
    /**
     * Vindi plan repository interface
     *
     * @var VindiPlanRepositoryInterface
     */
    protected $vindiPlanRepository;

    /**
     * Product repository
     *
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * Constructor
     *
     * @param VindiPlanRepositoryInterface $vindiPlanRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(
        VindiPlanRepositoryInterface $vindiPlanRepository,
        ProductRepository $productRepository
    ) {
        $this->vindiPlanRepository = $vindiPlanRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Render the select dropdown with recurrence plans
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        try {
            $productId = $row->getData('entity_id');
            $product = $this->productRepository->getById($productId);

            if ($product->getData('vindi_enable_recurrence') === '1') {
                $recurrenceDataJson = $product->getData('vindi_recurrence_data');

                if (!empty($recurrenceDataJson)) {
                    $recurrenceData = json_decode($recurrenceDataJson, true);

                    $recurrenceData = array_filter($recurrenceData, function($data) {
                        return isset($data['price']) && is_numeric($data['price']) && $data['price'] > 0;
                    });

                    // Generate the HTML for the select dropdown
                    $html = '<select name="product[' . $productId . '][selected_plan_id]" class="input-select admin__control-select selected_plan_id">';
                    $html .= '<option value="">' . __('-- Select --') . '</option>';
                    foreach ($recurrenceData as $data) {
                        $plan = $this->vindiPlanRepository->getById($data['plan']);
                        $planName = $plan ? $plan->getName() : $data['plan'];

                        $selected = '';

                        $html .= '<option value="' . $data['plan'] . '"' . $selected . '>' . $planName . '</option>';
                    }
                    $html .= '</select>';

                    return $html;
                } else {
                    return __('No recurrence plans available.');
                }
            } else {
                return __('Recurrence is not enabled for this product.');
            }
        } catch (NoSuchEntityException $e) {
            return __('Product not found.');
        }
    }
}
