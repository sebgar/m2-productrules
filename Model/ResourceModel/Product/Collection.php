<?php
namespace Sga\ProductRules\Model\ResourceModel\Product;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    protected $_attributeAdded = array();

    public function addAttributeAdded($attributeCode)
    {
        $this->_attributeAdded[$attributeCode] = true;
        return $this;
    }

    public function isAttributeAlreadyAdded($attributeCode)
    {
        return isset($this->_attributeAdded[$attributeCode]) && $this->_attributeAdded[$attributeCode] == true ? true : false;
    }

    public function getAttributeConditionSql($attribute, $condition = null, $joinType = 'inner')
    {
        return $this->_getAttributeConditionSql($attribute, $condition, $joinType);
    }

    public function getMappedAttributeCode($attributeCode)
    {
        $v = '';

        if (isset($this->_joinAttributes[$attributeCode]['condition_alias'])) {
            $v = (string)$this->_joinAttributes[$attributeCode]['condition_alias'];
        } elseif (isset($this->_joinFields[$attributeCode]['field'])) {
            $v = $this->_joinFields[$attributeCode]['field'];
        } elseif (isset($this->_staticFields[$attributeCode])) {
            $v = 'e.'.$this->_staticFields[$attributeCode];
        } elseif (isset($this->_selectAttributes[$attributeCode])) {
            $v = $attributeCode;
        }

        return $v;
    }
}
