<?php
namespace Sga\ProductRules\Model\Rule\Condition\Product;

use Sga\ProductRules\Model\Rule\Condition\Product;
use Sga\ProductRules\Model\Rule\ConditionInterface;
use Sga\ProductRules\Model\ResourceModel\Product\Collection as ProductCollection;

class HasPricePromo extends Product implements ConditionInterface
{
    public function loadArray($arr)
    {
        parent::loadArray($arr);
        $this->setOperatorType(isset($arr['operator_type']) ? $arr['operator_type'] : 'percent');
        return $this;
    }

    public function asArray(array $arrAttributes = array())
    {
        $out = parent::asArray($arrAttributes);
        $out['operator_type'] = $this->getOperatorType();
        return $out;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            '='  => __('is'),
            '!='  => __('is not'),
            '>='  => __('equals or greater than'),
            '<='  => __('equals or less than'),
            '>'   => __('greater than'),
            '<'   => __('less than'),
        ));
        return $this;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml();

        $html .= __(
            "Product has price promo with action %1 %2 %3",
            $this->getOperatorElement()->getHtml(),
            $this->getValueElement()->getHtml(),
            $this->getOperatorTypeElement()->getHtml()
        );

        if ($this->getId() != '1') {
            $html.= $this->getRemoveLinkHtml();
        }

        return $html;
    }

    public function getOperatorTypeElement()
    {
        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'__operator_type', 'select', array(
            'name' => 'rule['.$this->getPrefix().']['.$this->getId().'][operator_type]',
            'values' => $this->getOperatorTypeOptions(),
            'value' => $this->getOperatorType(),
            'value_name' => $this->getOperatorTypeName(),
            'data-form-part' => $this->getFormName()
        ))->setRenderer($this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class));
    }

    public function getOperatorTypeOptions()
    {
        return array(
            'percent' => __('Percent'),
            'fixed' => __('Fixed'),
        );
    }

    public function getOperatorTypeName()
    {
        $types = $this->getOperatorTypeOptions();

        $type = $this->getOperatorType();
        foreach ($types as $key => $label) {
            if ($key == $type) {
                return $label;
            }
        }

        return $type;
    }

    public function getOperatorType()
    {
        $value = $this->getData('operator_type');
        if (!isset($value)) {
            $types = $this->getOperatorTypeOptions();
            foreach ($types as $key => $label) {
                $this->setData('operator_type', $key);
                return $key;
            }
        }
        return $value;
    }

    public function computeCollectionFilters()
    {
        $this->_joinHasPricePromoCollection($this->getRule()->getProductCollection());
        return array();
    }

    protected function _joinHasPricePromoCollection(ProductCollection $collection)
    {
        $fromPart = $collection->getSelect()->getPart(\Zend_Db_Select::FROM);
        if (!isset($fromPart['cpip'])) {
            if ($this->getOperatorType() == 'percent') {
                $columns = array('cpip_final_price' => '(100-((cpip.final_price*100)/cpip.price))');
            } else {
                $columns = array('cpip_final_price' => '(cpip.price - cpip.final_price)');
            }

            // Liaison product <=> catalog_product_index_price
            $collection->getSelect()->join(
                array('cpip' => $collection->getTable('catalog_product_index_price')),
                join(' AND ', array('e.entity_id=cpip.entity_id')),
                $columns
            );

            // Add conditions
            $number = (float)$this->_cleanValues($this->getValue());
            $collection->getSelect()->where($columns['cpip_final_price'] . ' ' . $this->getOperator() . ' '.$number);

            // Add store condition
            $storeId = $this->getRule()->getStoreId();
            if ($storeId > 0) {
                $website = $this->_storeManager->getStore($storeId)->getWebsite();
                $collection->getSelect()->where('cpip.website_id = '.$website->getId());
            }

            $collection->getSelect()->group('e.entity_id');
        }
    }
}
