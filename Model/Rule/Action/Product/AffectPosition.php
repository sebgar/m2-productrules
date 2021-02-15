<?php
namespace Sga\ProductRules\Model\Rule\Action\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Sga\ProductRules\Model\Rule\ActionInterface;

class AffectPosition extends Attribute implements ActionInterface
{
    public function loadArray(array $arr)
    {
        $arr['value'] = '';
        parent::loadArray($arr);
        return $this;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml();

        $html .= __('%1 and affect Collection Position to attribute %2',
            $this->getOperatorElement()->getHtml(),
            $this->getAttributeElement()->getHtml()
        );

        $html .= $this->getRemoveLinkHtml();
        return $html;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption([
            '!=' => __('Do not delete all attribute value'),
            '=' => __('Delete all attribute value')
        ]);
        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(false);
        return $element;
    }

    public function getAttributeSelectOptions()
    {
        return $this->loadAttributeOptions()->getAttributeOption();
    }

    public function processOnCollection(ProductCollection $collection)
    {
        // Delete all attribute into table
        $this->_deleteAttributeValues($collection);

        // Fill all attribute
        $this->_fillAttributeValue($collection);
    }

    protected function _deleteAttributeValues(ProductCollection $collection)
    {
        if ($this->getOperator() === '=') {
            $attribute = $this->_productAttributeRepository->get($this->getAttribute());
            $typeAccepted = ['datetime','decimal','int','text','varchar'];

            if (in_array($attribute->getBackendType(), $typeAccepted)) {
                $adapter = $collection->getResource()->getConnection();

                $cond = [
                    'store_id=?' => $collection->getStoreId(),
                    'attribute_id=?' => $attribute->getAttributeId()
                ];
                $adapter->delete($collection->getTable('catalog_product_entity_'.$attribute->getBackendType()), $cond);
            }
        }
    }

    protected function _fillAttributeValue(ProductCollection $collection)
    {
        $i = 1;
        foreach ($collection->getItems() as $item) {
            $item->setStoreId($collection->getStoreId());
            $item->setData($this->getAttribute(), $i);
            $item->getResource()->saveAttribute($item, $this->getAttribute());

            $i++;
        }
    }
}
