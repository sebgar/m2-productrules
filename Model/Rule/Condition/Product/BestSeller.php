<?php
namespace Sga\ProductRules\Model\Rule\Condition\Product;

use Magento\Catalog\Model\ProductCategoryList;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Sga\ProductRules\Helper\Config as ConfigHelper;
use Sga\ProductRules\Helper\Data as DataHelper;
use Sga\ProductRules\Model\Rule\Condition\Product;
use Sga\ProductRules\Model\Rule\ConditionInterface;
use Sga\ProductRules\Model\ResourceModel\Product\Collection as ProductCollection;

class BestSeller extends Product implements ConditionInterface
{
    const COLUMN_SAVE_COUNT = 'sales_count';

    protected $_states;
    protected $_statuses;
    protected $_orderConfig;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        ConfigHelper $configHelper,
        DataHelper $dataHelper,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        OrderConfig $orderConfig,
        array $data = [],
        ProductCategoryList $categoryList = null
    ) {
        $this->_orderConfig = $orderConfig;

        parent::__construct($context, $backendData, $config, $productFactory, $productRepository, $productResource, $attrSetCollection, $localeFormat, $configHelper, $dataHelper, $storeManager, $dateTime, $data, $categoryList);
    }

    public function loadArray($arr)
    {
        parent::loadArray($arr);
        $this->setState(isset($arr['state']) ? $arr['state'] : array());
        $this->setOperatorState(isset($arr['operator_state']) ? $arr['operator_state'] : 'in');
        $this->setStatus(isset($arr['status']) ? $arr['status'] : array());
        $this->setOperatorStatus(isset($arr['operator_status']) ? $arr['operator_status'] : 'in');
        return $this;
    }

    public function asArray(array $arrAttributes = array())
    {
        $out = parent::asArray($arrAttributes);
        $out['state'] = $this->getState();
        $out['operator_state'] = $this->getOperatorState();
        $out['status'] = $this->getStatus();
        $out['operator_status'] = $this->getOperatorStatus();
        return $out;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            'all'  => __('ever'),
            'hour'  => __('hour'),
            'day'  => __('day')
        ));
        return $this;
    }

    public function loadOperatorStateOptions()
    {
        $this->setOperatorStateOption(array(
            'in'  => __('is in'),
            'nin'  => __('is not in')
        ));
        return $this;
    }

    public function loadOperatorStatusOptions()
    {
        $this->setOperatorStatusOption(array(
            'in'  => __('is in'),
            'nin'  => __('is not in')
        ));
        return $this;
    }

    public function getStateElement()
    {
        $stateSelected = $this->getStateSelected();

        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'__state', 'multiselect', array(
            'name' => 'rule['.$this->getPrefix().']['.$this->getId().'][state][]',
            'values' => $this->getStateOptions(),
            'value' => (is_array($stateSelected) ? implode(',', $stateSelected) : $stateSelected),
            'value_name' => $this->getStateName(),
            'data-form-part' => $this->getFormName()
        ))->setRenderer($this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class));
    }

    public function getOperatorStateElement()
    {
        $this->loadOperatorStateOptions();

        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'__operator_state', 'select', array(
            'name' => 'rule['.$this->getPrefix().']['.$this->getId().'][operator_state]',
            'values' => $this->getOperatorStateOption(),
            'value' => $this->getOperatorState(),
            'value_name' => $this->getOperatorStateName(),
            'data-form-part' => $this->getFormName()
        ))->setRenderer($this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class));
    }

    public function getStateOptions()
    {
        if (!isset($this->_states)) {
            $this->_states['all'] = array(
                'value' => 'all',
                'label' => __('All States')->getText()
            );

            $states = $this->_orderConfig->getStates();
            foreach ($states as $key => $info) {
                $this->_states[$key] = array(
                    'value' => $key,
                    'label' => $info->getText()
                );
            }
        }
        return $this->_states;
    }

    public function getStateSelected()
    {
        $states = $this->getStateOptions();

        $stateSelected = $this->getData('state');
        if (!isset($stateSelected)) {
            foreach ($states as $label) {
                $stateSelected[] = $label['value'];
                break;
            }
        } elseif (is_string($stateSelected)) {
            $stateSelected = (array)$stateSelected;
        }

        return $stateSelected;
    }

    public function getStateName()
    {
        $states = $this->getStateOptions();

        $stateSelected = $this->getStateSelected();

        $labels = array();
        foreach ($states as $label) {
            if (in_array($label['value'], $stateSelected)) {
                $labels[] = $label['label'];
            }
        }

        return implode(',', $labels);
    }

    public function getOperatorStateName()
    {
        $this->loadOperatorStateOptions();

        $operatorState = $this->getOperatorState();
        foreach ($this->getOperatorStateOption() as $key => $label) {
            if ($key == $operatorState) {
                return $label;
            }
        }

        return $operatorState;
    }

    public function getOperatorState()
    {
        $this->loadOperatorStateOptions();

        $value = $this->getData('operator_state');
        if (!isset($value)) {
            $types = $this->getOperatorStateOption();
            foreach ($types as $key => $label) {
                return $key;
            }
        }
        return $value;
    }

    public function getStatusElement()
    {
        $statusSelected = $this->getStatusSelected();

        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'__status', 'multiselect', array(
            'name' => 'rule['.$this->getPrefix().']['.$this->getId().'][status][]',
            'values' => $this->getStatusOptions(),
            'value' => (is_array($statusSelected) ? implode(',', $statusSelected) : $statusSelected),
            'value_name' => $this->getStatusName(),
            'data-form-part' => $this->getFormName()
        ))->setRenderer($this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class));
    }

    public function getOperatorStatusElement()
    {
        $this->loadOperatorStatusOptions();

        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'__operator_status', 'select', array(
            'name' => 'rule['.$this->getPrefix().']['.$this->getId().'][operator_status]',
            'values' => $this->getOperatorStatusOption(),
            'value' => $this->getOperatorStatus(),
            'value_name' => $this->getOperatorStatusName(),
            'data-form-part' => $this->getFormName()
        ))->setRenderer($this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class));
    }

    public function getStatusOptions()
    {
        if (!isset($this->_statuses)) {
            $this->_statuses['all'] = array(
                'value' => 'all',
                'label' => __('All Statuses')->getText()
            );

            $statuses = $this->_orderConfig->getStatuses();;
            foreach ($statuses as $key => $label) {
                $this->_statuses[$key] = array(
                    'value' => $key,
                    'label' => $label
                );
            }
        }
        return $this->_statuses;
    }

    public function getStatusSelected()
    {
        $statuses = $this->getStatusOptions();

        $statusSelected = $this->getData('status');
        if (!isset($statusSelected)) {
            foreach ($statuses as $label) {
                $statusSelected[] = $label['value'];
                break;
            }
        } elseif (is_string($statusSelected)) {
            $statusSelected = (array)$statusSelected;
        }

        return $statusSelected;
    }

    public function getStatusName()
    {
        $statuses = $this->getStatusOptions();

        $statusSelected = $this->getStatusSelected();

        $labels = array();
        foreach ($statuses as $label) {
            if (in_array($label['value'], $statusSelected)) {
                $labels[] = $label['label'];
            }
        }

        return implode(',', $labels);
    }

    public function getOperatorStatusName()
    {
        $this->loadOperatorStatusOptions();

        $operatorStatus = $this->getOperatorStatus();
        foreach ($this->getOperatorStatusOption() as $key => $label) {
            if ($key == $operatorStatus) {
                return $label;
            }
        }

        return $operatorStatus;
    }

    public function getOperatorStatus()
    {
        $this->loadOperatorStatusOptions();

        $value = $this->getData('operator_status');
        if (!isset($value)) {
            $types = $this->getOperatorStatusOption();
            foreach ($types as $key => $label) {
                return $key;
            }
        }
        return $value;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml();

        $html .= __("Product best seller from %1 %2 where order %3 state %4 and %5 status %6",
            $this->getValueElement()->getHtml(),
            $this->getOperatorElement()->getHtml(),
            $this->getOperatorStateElement()->getHtml(),
            $this->getStateElement()->getHtml(),
            $this->getOperatorStatusElement()->getHtml(),
            $this->getStatusElement()->getHtml()
        );

        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return $html;
    }

    public function computeCollectionFilters()
    {
        $this->_joinBestSellerCollection($this->getRule()->getProductCollection());
        return array();
    }

    protected function _joinBestSellerCollection(ProductCollection $collection)
    {
        $fromPart = $collection->getSelect()->getPart(\Zend_Db_Select::FROM);
        if (!isset($fromPart['soi'])) {
            $conditions = array(
                'e.entity_id=soi.product_id',
            );

            $storeId = $this->getRule()->getStoreId();
            if ($storeId > 0) {
                $conditions[] = 'soi.store_id='.$storeId;
            }

            // Liaison product <=> sales_flat_order_item
            $collection->getSelect()->join(
                array('soi' => $collection->getTable('sales_order_item')),
                join(' AND ', $conditions),
                array(self::COLUMN_SAVE_COUNT => 'COUNT(soi.product_id)')
            );

            // Liaison sales_flat_order_item <=> sales_flat_order
            $stateSelected = $this->getStateSelected();
            $states = array();
            foreach ($stateSelected as $s) {
                if ($s != 'all') {
                    $states[] = $s;
                }
            }
            $statusSelected = $this->getStatusSelected();
            $statuses = array();
            foreach ($statusSelected as $s) {
                if ($s != 'all') {
                    $statuses[] = $s;
                }
            }
            if (count($states) > 0 || count($statuses) > 0) {
                $conditions = array('soi.order_id=so.entity_id');
                if (count($states) > 0) {
                    switch($this->getOperatorState()) {
                        case 'in':
                            $conditions[] = 'so.state IN ("'.implode('","', $states).'")';
                            break;
                        case 'nin':
                            $conditions[] = 'so.state NOT IN ("'.implode('","', $states).'")';
                            break;
                    }
                }
                if (count($statuses) > 0) {
                    switch($this->getOperatorStatus()) {
                        case 'in':
                            $conditions[] = 'so.status IN ("'.implode('","', $statuses).'")';
                            break;
                        case 'nin':
                            $conditions[] = 'so.status NOT IN ("'.implode('","', $statuses).'")';
                            break;
                    }
                }

                $collection->getSelect()->join(
                    array('so' => $collection->getTable('sales_order')),
                    join(' AND ', $conditions),
                    null
                );
            }

            // Date de creation
            if ($this->getOperator() != 'all') {
                $number = $this->_cleanValues($this->getValue());
                $timestamp = $this->_dateTime->gmtTimestamp();
                switch($this->getOperator()) {
                    case 'hour':
                        $timestamp = $timestamp - ($number * 60 * 60);
                        break;
                    case 'day':
                        $timestamp = $timestamp - ($number * 24 * 60 * 60);
                        break;
                }
                $dateRef = $this->_dateTime->gmtDate('Y-m-d', $timestamp).' 00:00:00';

                $collection->getSelect()->where('soi.created_at > "'.$dateRef.'"');
            }

            $collection->getSelect()->order(self::COLUMN_SAVE_COUNT.' DESC');
            $collection->getSelect()->group('e.entity_id');

            $this->_dataHelper->setColumnAffectedData(self::COLUMN_SAVE_COUNT);
        }
    }
}
