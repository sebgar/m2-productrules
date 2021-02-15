<?php
namespace Sga\ProductRules\Model\Rule\Condition\Product;

use Sga\ProductRules\Model\Rule\Condition\Product;
use Sga\ProductRules\Model\Rule\ConditionInterface;
use Sga\ProductRules\Model\ResourceModel\Product\Collection as ProductCollection;

class CreatedFrom extends Product implements ConditionInterface
{
    public function loadArray($arr)
    {
        parent::loadArray($arr);
        $this->setTime(isset($arr['time']) ? $arr['time'] : array());
        return $this;
    }

    public function asArray(array $arrAttributes = array())
    {
    	$out = parent::asArray($arrAttributes);
    	$out['time'] = $this->getTime();
    	return $out;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            '<='  => __('from more than'),
            '>='  => __('from less than')
        ));
        return $this;
    }

    public function getTimeElement()
    {
        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'__time', 'select', array(
            'name' => 'rule['.$this->getPrefix().']['.$this->getId().'][time]',
            'values' => $this->getTimeOptions(),
            'value' => $this->getTime(),
        	'value_name' => $this->getTimeName(),
            'data-form-part' => $this->getFormName()
        ))->setRenderer($this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class));
    }

    public function getTimeOptions()
    {
        return array(
            'hour' => __('hour'),
            'day' => __('day'),
        );
    }

    public function getTime()
    {
        $times = $this->getTimeOptions();

        $time = $this->getData('time');
        if (!isset($time)) {
            foreach ($times as $key => $label) {
                return $key;
            }
        }

        return $time;
    }

    public function getTimeName()
    {
        $times = $this->getTimeOptions();

        $time = $this->getTime();
        foreach ($times as $key => $label) {
            if ($key == $time) {
                return $label;
            }
        }

        return $time;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml();

        $html .= __("Product is created %1 %2 %3",
            $this->getOperatorElement()->getHtml(),
            $this->getValueElement()->getHtml(),
            $this->getTimeElement()->getHtml()
        );

        if ($this->getId() != '1') {
            $html.= $this->getRemoveLinkHtml();
        }

        return $html;
    }

    public function computeCollectionFilters()
    {
        $attributeCode = 'created_at';

        $attributeKey = $this->_joinAttributeCollection($this->getRule()->getProductCollection(), $attributeCode);
        $filters = array($attributeKey => $this->_getCollectionValue());

        return $filters;
    }

    protected function _getCollectionValue()
    {
        $number = $this->_cleanValues($this->getValue());
        $timestamp = $this->_dateTime->gmtTimestamp();
        switch($this->getTime()) {
            case 'hour':
                $timestamp = $timestamp - ($number * 60 * 60);
            break;
            case 'day':
                $timestamp = $timestamp - ($number * 24 * 60 * 60);
            break;
        }
        $dateRef = $this->_dateTime->gmtDate('Y-m-d', $timestamp).' 23:59:59';

        return array($this->getOperator() => $dateRef);
    }
}
