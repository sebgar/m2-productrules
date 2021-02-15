<?php
namespace Sga\ProductRules\Model\Rule\Action\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Sga\ProductRules\Model\Rule\Action\AbstractAction;
use Sga\ProductRules\Model\Rule\ActionInterface;

class Attribute extends AbstractAction implements ActionInterface
{
    public function getActionsForCollection()
    {
        $productAttributes = $this->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => \Sga\ProductRules\Model\Rule\Action\Product\Attribute::class.'|'.$code,
                'label' => $label
            ];
        }

        return $attributes;
    }

    public function loadArray(array $arr)
    {
        if (!isset($arr['operator'])) {
            $arr['operator'] = '';
        }
        if (!isset($arr['value'])) {
            $arr['value'] = '';
        }

        parent::loadArray($arr);
        return $this;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml();

        $html .= __(
            '%1 = %2',
            $this->getAttributeElement()->getHtml(),
            $this->getValueElement()->getHtml()
        );

        $html .= $this->getRemoveLinkHtml();

        return $html;
    }

    ///// START TAKE FROM Magento\Rule\Model\Condition\Product\AbstractProduct

    public function loadAttributeOptions($frontendInputs = [])
    {
        $searchCriteria = $this->_searchCriteriaBuilder->create();
        $productAttributes = $this->_productAttributeRepository->getList($searchCriteria);
        $attributesAllowed = $this->_configHelper->getAttributesAction();

        $attributes = [];
        foreach ($productAttributes->getItems() as $attribute) {
            if (in_array($attribute->getAttributeCode(), $attributesAllowed)) {
                if (count($frontendInputs) === 0 || in_array($attribute->getFrontendInput(), $frontendInputs)) {
                    $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
                }
            }
        }

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getValueAfterElementHtml()
    {
        $html = '';

        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                $image = $this->_assetRepo->getUrl('images/rule_chooser_trigger.gif');
                break;
        }

        if (!empty($image)) {
            $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' .
                $image .
                '" alt="" class="v-middle rule-chooser-trigger" title="' .
                __('Open Chooser') . '" /></a>';
        }
        return $html;
    }

    public function getExplicitApply()
    {
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    return true;
                default:
                    break;
            }
        }
        return false;
    }

    public function getValueSelectOptions()
    {
        $this->_prepareValueOptions();
        return $this->getData('value_select_options');
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
            if ($attributeObject->usesSource()) {
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

    protected function _setSelectOptions($selectOptions, $selectReady, $hashedReady)
    {
        if ($selectOptions !== null) {
            // Overwrite only not already existing values
            if (!$selectReady) {
                $this->setData('value_select_options', $selectOptions);
            }
            if (!$hashedReady) {
                $hashedOptions = [];
                foreach ($selectOptions as $option) {
                    if (is_array($option['value'])) {
                        continue; // We cannot use array as index
                    }
                    $hashedOptions[$option['value']] = $option['label'];
                }
                $this->setData('value_option', $hashedOptions);
            }
        }
        return $this;
    }

    public function getAttributeObject()
    {
        try {
            $obj = $this->_productAttributeRepository->get($this->getAttribute());
        } catch (\Exception $e) {
            $obj = new \Magento\Framework\DataObject();
            $obj->setEntity($this->_productFactory->create())->setFrontendInput('text');
        }
        return $obj;
    }

    public function getValueElementType()
    {
        if (!is_object($this->getAttributeObject())) {
            return 'text';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
            case 'boolean':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            default:
                return 'text';
        }
    }

    public function getInputType()
    {
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            case 'boolean':
                return 'boolean';

            default:
                return 'string';
        }
    }
    ///// END TAKE FROM Magento\Rule\Model\Condition\Product\AbstractProduct

    public function processOnCollection(ProductCollection $collection)
    {
        $attribute = $this->getAttributeObject();

        foreach ($collection->getItems() as $item) {
            $item->setStoreId($collection->getStoreId());
            if ($attribute->getBackendType() == 'datetime') {
                $date = $this->_dateTime->gmtDate('Y-m-d H:i:s', $this->getValue());
                $item->setData($this->getAttribute(), $date);
            } elseif ($attribute->getBackendModel() == 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend') {
                $item->setData($this->getAttribute(), is_array($this->getValue()) ? implode(',', $this->getValue()) : $this->getValue());
            } else {
                $item->setData($this->getAttribute(), $this->getValue());
            }
            $item->getResource()->saveAttribute($item, $this->getAttribute());
        }
    }
}
