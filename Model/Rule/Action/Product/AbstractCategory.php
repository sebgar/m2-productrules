<?php
namespace Sga\ProductRules\Model\Rule\Action\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Sga\ProductRules\Model\Rule\Action\AbstractAction;
use Sga\ProductRules\Model\Rule\ActionInterface;

abstract class AbstractCategory extends AbstractAction implements ActionInterface
{
    public function getInputType()
    {
        return 'category';
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function getExplicitApply()
    {
        return true;
    }

    public function getValueAfterElementHtml()
    {
        $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' .
            $this->_assetRepo->getUrl('images/rule_chooser_trigger.gif') .
            '" alt="" class="v-middle rule-chooser-trigger" title="'.__('Open Chooser') . '" /></a>';
        return $html;
    }

    public function getChooserContainerHtml()
    {
        $url = 'catalog_rule/promo_widget/chooser/attribute/category_ids';
        if ($this->getJsFormObject()) {
            $url .= '/form/' . $this->getJsFormObject();
        } else {
            $jsFormObject = $this->getRule()->getActionsFieldSetId($this->getFormName());
            $url .= '/form/' . $jsFormObject;
        }

        $url = $this->_urlBuilder->getUrl($url);
        return '<div class="rule-chooser" url="'.$url.'"></div>';
    }

    protected function _getCategoryIds()
    {
        return explode(',', $this->getValue());
    }
}
