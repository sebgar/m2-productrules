<?php
namespace Sga\ProductRules\Model\Rule\Condition;

use Magento\Framework\Event\ManagerInterface;
use Magento\Rule\Model\Condition\Context;
use Sga\ProductRules\Model\Rule\ConditionInterface;
use Sga\ProductRules\Helper\Data as DataHelper;

class Combine extends \Magento\Rule\Model\Condition\Combine implements ConditionInterface
{
    protected $_eventManager;
    protected $_dataHelper;

    public function __construct(
        Context $context,
        ManagerInterface $eventManager,
        DataHelper $dataHelper,
        array $data = []
    ) {
        $this->_eventManager = $eventManager;
        $this->_dataHelper = $dataHelper;

        parent::__construct($context, $data);

        $this->setType(\Sga\ProductRules\Model\Rule\Condition\Combine::class);
    }

    public function getNewChildSelectOptions()
    {
        $parentConditions = parent::getNewChildSelectOptions();
        $paramConditions = $this->_dataHelper->getConditionsForCombine();

        if (count($paramConditions) > 0) {
            return array_merge_recursive($parentConditions, $paramConditions);
        } else {
            return $parentConditions;
        }
    }

    public function asHtmlRecursive()
    {
        $html = $this->asHtml() .
            '<ul id="' .
            $this->getPrefix() .
            '__' .
            $this->getId() .
            '__children" class="rule-param-children">';
        foreach ($this->getConditions() as $cond) {
            $cond->setFormName($this->getFormName());
            $html .= '<li>' . $cond->asHtmlRecursive() . '</li>';
        }
        $html .= '<li>' . $this->getNewChildElement()->getHtml() . '</li></ul>';
        return $html;
    }

    public function computeCollectionFilters()
    {
        if (!$this->getConditions()) {
            return true;
        }

        $all = ($this->getAggregator() === 'all' ? 'and' : 'or');

        $filters = array(
            'operator' => $all,
            'list' => array()
        );
        foreach ($this->getConditions() as $cond) {
            $filters['list'][] = $cond->computeCollectionFilters();
        }

        return $filters;
    }
}
