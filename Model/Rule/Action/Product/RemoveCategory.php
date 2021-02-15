<?php
namespace Sga\ProductRules\Model\Rule\Action\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Sga\ProductRules\Model\Rule\ActionInterface;

class RemoveCategory extends AbstractCategory implements ActionInterface
{
    public function loadArray(array $arr)
    {
        $arr['operator'] = '';
        $arr['attribute'] = '';
        parent::loadArray($arr);
        return $this;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml();

        $html .= __('Remove products from categories %1',
            $this->getValueElement()->getHtml()
        );

        $html .= $this->getRemoveLinkHtml();
        $html .= $this->getChooserContainerHtml();
        return $html;
    }

    public function processOnCollection(ProductCollection $collection)
    {
        $this->_removeCategories($collection);
    }

    protected function _removeCategories(ProductCollection $collection)
    {
        $ids = $this->_getCategoryIds();
        if (count($ids) > 0) {
            $adapter = $collection->getResource()->getConnection();

            $cond = [
                'category_id IN (?)' => $ids,
                'product_id IN (?)' => $collection->getAllIds()
            ];
            $adapter->delete($collection->getTable('catalog_category_product'), $cond);
        }
    }
}
