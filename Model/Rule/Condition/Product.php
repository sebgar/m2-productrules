<?php
namespace Sga\ProductRules\Model\Rule\Condition;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductCategoryList;
use Magento\Rule\Model\Condition\Product\AbstractProduct;
use Sga\ProductRules\Model\ResourceModel\Product\Collection as ProductCollection;
use Sga\ProductRules\Model\Rule\ConditionInterface;
use Sga\ProductRules\Helper\Config as ConfigHelper;
use Sga\ProductRules\Helper\Data as DataHelper;

class Product extends AbstractProduct implements ConditionInterface
{
    protected $_configHelper;
    protected $_dataHelper;
    protected $_storeManager;
    protected $_dateTime;

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
        array $data = [],
        ProductCategoryList $categoryList = null
    ){
        $this->_configHelper = $configHelper;
        $this->_dataHelper = $dataHelper;
        $this->_storeManager = $storeManager;
        $this->_dateTime = $dateTime;

        parent::__construct($context, $backendData, $config, $productFactory, $productRepository, $productResource, $attrSetCollection, $localeFormat, $data, $categoryList);
    }

    public function getConditionsForCombine()
    {
        $productAttributes = $this->loadAttributeOptions()->getAttributeOption();

        $attributes = array();
        foreach ($productAttributes as $code => $label) {
            $attributes[] = array(
                'value' => \Sga\ProductRules\Model\Rule\Condition\Product::class.'|'.$code,
                'label' => $label
            );
        }

        return $attributes;
    }

    public function loadAttributeOptions()
    {
        $productAttributes = $this->_productResource->loadAllAttributes()->getAttributesByCode();
        $attributesAllowed = $this->_configHelper->getAttributesCondition();

        $attributes = [];
        foreach ($productAttributes as $attribute) {
            if (in_array($attribute->getAttributeCode(), $attributesAllowed)) {
                $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            }
        }

        $this->_addSpecialAttributes($attributes);

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                $url = 'catalog_rule/promo_widget/chooser/attribute/' . $this->getAttribute();

                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                } else {
                    $jsFormObject = $this->getRule()->getConditionsFieldSetId($this->getFormName());
                    $url .= '/form/' . $jsFormObject;
                }

                break;
            default:
                break;
        }
        return $url !== false ? $this->_backendData->getUrl($url) : '';
    }

    protected function _prepareValueOptions()
    {
        // Check that both keys exist. Maybe somehow only one was set not in this routine, but externally.
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');
        if ($selectReady && $hashedReady) {
            return $this;
        }

        // Get array of select options. It will be used as source for hashed options
        $selectOptions = null;
        if ($this->getAttribute() === 'attribute_set_id') {
            $entityTypeId = $this->_config->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();
            $selectOptions = $this->_attrSetCollection
                ->setEntityTypeFilter($entityTypeId)
                ->load()
                ->toOptionArray();
        } elseif ($this->getAttribute() === 'type_id') {
            foreach ($selectReady as $value => $label) {
                if (is_array($label) && isset($label['value'])) {
                    $selectOptions[] = $label;
                } else {
                    $selectOptions[] = ['value' => $value, 'label' => $label];
                }
            }
            $selectReady = null;
        } elseif (is_object($this->getAttributeObject())) {
            $attributeObject = $this->getAttributeObject();
            if ($attributeObject instanceof \Magento\Eav\Model\Attribute && $attributeObject->usesSource()) {
                if ($attributeObject->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
            }
        }

        $this->_setSelectOptions($selectOptions, $selectReady, $hashedReady);

        return $this;
    }

    public function computeCollectionFilters()
    {
        $attributeCode = $this->getAttribute();

        if ('category_ids' == $attributeCode) {
            $attributeKey = $this->_joinCategoryCollection($this->getRule()->getProductCollection());
            $filters = array($attributeKey => $this->_getCollectionValue());
        } else {
            $attributeKey = $this->_joinAttributeCollection($this->getRule()->getProductCollection(), $attributeCode);
            $filters = array($attributeKey => $this->_getCollectionValue());
        }

        return $filters;
    }

    protected function _joinCategoryCollection(ProductCollection $collection)
    {
        $conditions = array(
            'cat_index.product_id=e.entity_id',
        );

        $fromPart = $collection->getSelect()->getPart(\Zend_Db_Select::FROM);
        if (isset($fromPart['cat_index'])) {
            $fromPart['cat_index']['joinCondition'] = join(' AND ', $conditions);
            $collection->getSelect()->setPart(\Zend_Db_Select::FROM, $fromPart);
        } else {
            $collection->getSelect()->join(
                array('cat_index' => $collection->getTable('catalog_category_product')),
                join(' AND ', $conditions),
                array('cat_index_position' => 'position')
            );

            $collection->getSelect()->columns('cat_index.category_id as category_id');
            $collection->getSelect()->group('e.entity_id');
        }

        return 'cat_index.category_id';
    }

    protected function _joinAttributeCollection(ProductCollection $collection, $attributeCode)
    {
        $collection->addAttributeToSelect($attributeCode, 'left');
        return $collection->getMappedAttributeCode($attributeCode);
    }

    protected function _getCollectionValue()
    {
        if ($this->_isStringDate()) {
            // On n'utilise pas core/date::gmtDate car cela introduit des decalages
            $value = date('Y-m-d H:i:s', strtotime($this->getValue()));
        } else {
            $value = $this->getValue();
        }

        $attribute = $this->getAttributeObject();
        if ('category_ids' == $this->getAttribute()) {
            $isNumber = true;
        } else {
            $isNumber = in_array($attribute->getBackendType(), array('int','decimal')) ? true : false;
        }
        switch($this->getOperator()) {
            case '()':
                if ($attribute->getFrontendInput() == 'multiselect') {
                    if (is_array($value)) {
                        return array('finset' => $this->_cleanValues($value, $isNumber));
                    } else {
                        return array('finset' => $this->_cleanValues(explode(',', $value), $isNumber));
                    }
                } else {
                    if (is_array($value)) {
                        return array('in' => $this->_cleanValues($value, $isNumber));
                    } else {
                        return array('in' => $this->_cleanValues(explode(',', $value), $isNumber));
                    }
                }
                break;
            case '!()':
                if ($attribute->getFrontendInput() == 'multiselect') {
                    if (is_array($value)) {
                        return array('nfinset' => $this->_cleanValues($value, $isNumber));
                    } else {
                        return array('nfinset' => $this->_cleanValues(explode(',', $value), $isNumber));
                    }
                } else {
                    if (is_array($value)) {
                        return array('nin' => $this->_cleanValues($value, $isNumber));
                    } else {
                        return array('nin' => $this->_cleanValues(explode(',', $value), $isNumber));
                    }
                }
                break;
            case '{}':
                return array('like' => $this->_cleanValues($value, $isNumber));
                break;
            case '!{}':
                return array('nlike' => $this->_cleanValues($value, $isNumber));
                break;
            default:
                return array($this->getOperator() => $this->_cleanValues($value, $isNumber));
                break;
        }
    }

    protected function _isStringDate()
    {
        if ($this->getInputType() == 'date' && trim($this->getData('value')) != '' && !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', trim($this->getData('value')))) {
            return true;
        }
        return false;
    }

    protected function _cleanValues($values, $isNumber = false)
    {
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                $values[$key] = $isNumber ? (float) trim($value) : trim($value);
            }
        } else {
            $values = $isNumber ? (float) trim($values) : trim($values);
        }

        return $values;
    }
}
