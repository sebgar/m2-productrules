<?php
namespace Sga\ProductRules\Model\Rule\Condition\Product;

use Sga\ProductRules\Model\Rule\Condition\Product;
use Sga\ProductRules\Model\Rule\ConditionInterface;

class IsNew extends Product implements ConditionInterface
{
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml();

        $html .= __(
            "Product %1 new",
            $this->getOperatorElement()->getHtml()
        );

        if ($this->getId() != '1') {
            $html.= $this->getRemoveLinkHtml();
        }

        return $html;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            '==' => __('is'),
            '!=' => __('is not'),
        ));
        return $this;
    }

    public function computeCollectionFilters()
    {
        $attributeFrom = 'news_from_date';
        $attributeTo = 'news_to_date';

        $attributeFrom = $this->_joinAttributeCollection($this->getRule()->getProductCollection(), $attributeFrom);
        $attributeTo = $this->_joinAttributeCollection($this->getRule()->getProductCollection(), $attributeTo);

        $timestamp = $this->_dateTime->gmtTimestamp();
        $dateNowFrom = $this->_dateTime->gmtDate('Y-m-d', $timestamp).' 00:00:00';
        $dateNowTo = $this->_dateTime->gmtDate('Y-m-d', $timestamp).' 23:59:59';

        $where = array();
        if ($this->getOperator() === '==') {
            $where[] = '('.$attributeFrom.' IS NOT NULL AND '.$attributeFrom.' <= "'.$dateNowFrom.'" AND '.$attributeTo.' IS NULL)';
            $where[] = '('.$attributeFrom.' IS NULL AND '.$attributeTo.' IS NOT NULL AND '.$attributeTo.' >= "'.$dateNowTo.'")';
            $where[] = '('.$attributeFrom.' IS NOT NULL AND '.$attributeFrom.' <= "'.$dateNowFrom.'" AND '.$attributeTo.' IS NOT NULL AND '.$attributeTo.' >= "'.$dateNowTo.'")';
        } else {
            $where[] = '('.$attributeFrom.' IS NOT NULL AND '.$attributeFrom.' > "'.$dateNowFrom.'" AND '.$attributeTo.' IS NULL)';
            $where[] = '('.$attributeFrom.' IS NULL AND '.$attributeTo.' IS NOT NULL AND '.$attributeTo.' < "'.$dateNowTo.'")';
            $where[] = '('.$attributeFrom.' IS NULL AND '.$attributeTo.' IS NULL)';
            $where[] = '('.$attributeFrom.' IS NOT NULL AND '.$attributeTo.' IS NOT NULL AND ('.$attributeFrom.' > "'.$dateNowFrom.'" OR '.$attributeTo.' < "'.$dateNowTo.'"))';
        }

        $this->getRule()->getProductCollection()->getSelect()->where('('.implode(' OR ', $where).')');

        return array();
    }
}
