<?php
namespace Sga\ProductRules\Model\Rule\Action;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Sga\ProductRules\Model\Rule\ActionInterface;

class Collection extends AbstractAction implements ActionInterface
{
    protected function _construct()
    {
        parent::_construct();

        $this->setActions([]);
        $this->setType(\Sga\ProductRules\Model\Rule\Action\Collection::class);
    }

    public function getNewChildSelectOptions()
    {
        $parentActions = parent::getNewChildSelectOptions();
        $paramActions = $this->_dataHelper->getActionsForCollection();

        if (count($paramActions) > 0) {
            return array_merge_recursive($parentActions, $paramActions);
        } else {
            return $parentActions;
        }
    }

    /**
     * Returns array containing actions in the collection
     *
     * Output example:
     * array(
     *   {action::asArray},
     *   {action::asArray}
     * )
     *
     * @param array $arrAttributes
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function asArray(array $arrAttributes = [])
    {
        $out = parent::asArray();

        foreach ($this->getActions() as $item) {
            $out['actions'][] = $item->asArray();
        }
        return $out;
    }

    /**
     * Load array
     *
     * @param array $arr
     * @return \Magento\Rule\Model\Action\Collection
     */
    public function loadArray(array $arr)
    {
        if (!empty($arr['actions']) && is_array($arr['actions'])) {
            foreach ($arr['actions'] as $actArr) {
                if (empty($actArr['type'])) {
                    continue;
                }
                $action = $this->_actionFactory->create($actArr['type']);
                $action->loadArray($actArr);
                $this->addAction($action);
            }
        }
        return $this;
    }

    /**
     * Add actions
     *
     * @param \Magento\Rule\Model\Action\ActionInterface $action
     * @return $this
     */
    public function addAction(ActionInterface $action)
    {
        ///// BEGIN SGA
        $action->setRule($this->getRule());
        ///// END SGA
        $actions = $this->getActions();

        ///// BEGIN OLD
        //$action->setRule($this->getRule());
        ///// END OLD

        $actions[] = $action;
        if (!$action->getId()) {
            ///// BEGIN OLD
            //$action->setId($this->getId().'.'.sizeof($actions));
            ///// END OLD
            ///// BEGIN SGA
            $action->setId($this->getId().'--'.sizeof($actions));
            ///// END SGA
        }

        $this->setActions($actions);
        return $this;
    }

    /**
     * As html
     *
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->toHtml() . 'Perform following actions: ';
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    /**
     * Return new child element
     *
     * @return $this
     */
    public function getNewChildElement()
    {
        return $this->getForm()->addField(
            ///// BEGIN OLD
            //'action:' . $this->getId() . ':new_child',
            ///// END OLD
            ///// BEGIN SGA
            'action__' . $this->getId() . '__new_child',
            ///// END SGA
            'select',
            [
                'name' => $this->elementName . '[actions][' . $this->getId() . '][new_child]',
                'values' => $this->getNewChildSelectOptions(),
                'value_name' => $this->getNewChildName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Newchild::class)
        );
    }

    /**
     * Return as html recursive
     *
     * @return string
     */
    public function asHtmlRecursive()
    {
        ///// BEGIN OLD
        //$html = $this->asHtml() . '<ul id="action:' . $this->getId() . ':children">';
        ///// END OLD
        ///// BEGIN SGA
        $html = $this->asHtml() . '<ul id="action__' . $this->getId() . '__children">';
        ///// END SGA
        foreach ($this->getActions() as $cond) {
            $cond->setFormName($this->getFormName());
            $html .= '<li>' . $cond->asHtmlRecursive() . '</li>';
        }
        $html .= '<li>' . $this->getNewChildElement()->getHtml() . '</li></ul>';
        return $html;
    }

    /**
     * Add string
     *
     * @param string $format
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function asString($format = '')
    {
        $str = __("Perform following actions");
        return $str;
    }

    /**
     * Return string as recursive
     *
     * @param int $level
     * @return string
     */
    public function asStringRecursive($level = 0)
    {
        $str = $this->asString();
        foreach ($this->getActions() as $action) {
            $str .= "\n" . $action->asStringRecursive($level + 1);
        }
        return $str;
    }

    public function setJsFormObject($form)
    {
        $this->setData('js_form_object', $form);
        foreach ($this->getActions() as $action) {
            $action->setJsFormObject($form);
        }
        return $this;
    }

    public function processOnCollection(ProductCollection $collection)
    {
        foreach ($this->getActions() as $action) {
            $action->processOnCollection($collection);
        }
        return $this;
    }
}
