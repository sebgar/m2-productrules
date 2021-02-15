<?php
namespace Sga\ProductRules\Model\Rule\Action\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Sga\ProductRules\Model\Rule\ActionInterface;

class AttributeMulti extends Attribute implements ActionInterface
{
    public function getActionsForCollection()
    {
        $productAttributes = $this->loadAttributeOptions(['multiselect'])->getAttributeOption();
        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => \Sga\ProductRules\Model\Rule\Action\Product\AttributeMulti::class.'|'.$code,
                'label' => $label
            ];
        }

        return $attributes;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml();

        $html .= __(
            '%1 : %2 %3',
            $this->getAttributeElement()->getHtml(),
            $this->getOperatorElement()->getHtml(),
            $this->getValueElement()->getHtml()
        );

        $html .= $this->getRemoveLinkHtml();

        return $html;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption([
            '='  => __('Add value(s)'),
            '!='  => __('Remove value(s)')
        ]);
        return $this;
    }

    public function processOnCollection(ProductCollection $collection)
    {
        $attribute = $this->getAttributeObject();

        if ($attribute->getFrontendInput() === 'multiselect') {
            $this->_loadAttributeValueForCollection($collection);

            foreach ($collection->getItems() as $item) {
                $item->setStoreId($this->_getStoreId());

                $itemValue = $item->getData($this->getAttribute());

                // parse existing values
                if ($itemValue === null) {
                    $itemValues = array();
                } elseif (is_string($itemValue) && $itemValue != '') {
                    $itemValues = explode(',', $itemValue);
                } else {
                    $itemValues = array($itemValue);
                }

                if (is_string($this->getValue())) {
                    $values = explode(',', $this->getValue());
                } else {
                    $values = $this->getValue();
                }

                if ($this->getOperator() === '=') {
                    // add value(s)
                    foreach ($values as $val) {
                        if (!in_array($val, $itemValues)) {
                            $itemValues[] = $val;
                        }
                    }
                } elseif ($this->getOperator() === '!=') {
                    // remove value(s)
                    foreach ($values as $value) {
                        $pos = array_search($value, $itemValues);
                        if ($pos !== false) {
                            unset($itemValues[$pos]);
                        }
                    }
                }

                // clean empty value(s)
                foreach ($itemValues as $key => $value) {
                    if ((string)$value === '') {
                        unset($itemValues[$key]);
                    }
                }

                if (count($itemValues) == 0) {
                    $itemValues = null;
                } else {
                    $itemValues = implode(',', $itemValues);
                }

                // set data
                $item->setData($this->getAttribute(), $itemValues);

                // save in database
                $item->getResource()->saveAttribute($item, $this->getAttribute());
            }
        }
    }

    protected function _loadAttributeValueForCollection(ProductCollection $collection)
    {
        // check if attribute value is already added
        if ($collection->isAttributeAlreadyAdded($this->getAttribute())) {
            return $this;
        }

        // get all ids
        $itemIds = array();
        foreach ($collection as $item) {
            $itemIds[] = $item->getId();
        }

        // load attribute into a new collection
        $collectionAttrValue = $this->_productCollectionFactory->create()
            ->addFieldToFilter('entity_id', array('in' => $itemIds))
            ->addAttributeToSelect($this->getAttribute())
            ->setStore($this->_getStoreId());

        // add attribute Value
        foreach ($collection as $itemId => $item) {
            $it = $collectionAttrValue->getItemById($itemId);
            if ($it !== null && $it->getId() == $itemId) {
                $item->setData($this->getAttribute(), $it->getData($this->getAttribute()));
            }
        }

        // flag attribute already added
        $collection->addAttributeAdded($this->getAttribute());

        return $this;
    }
}
