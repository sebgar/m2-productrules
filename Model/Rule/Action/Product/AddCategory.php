<?php
namespace Sga\ProductRules\Model\Rule\Action\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Sga\ProductRules\Model\Rule\ActionInterface;

class AddCategory extends AbstractCategory implements ActionInterface
{
    public function loadArray(array $arr)
    {
        $arr['attribute'] = '';
        parent::loadArray($arr);
        return $this;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml();

        $html .= __('%1 and add products into categories %2',
            $this->getOperatorElement()->getHtml(),
            $this->getValueElement()->getHtml()
        );

        $html .= $this->getRemoveLinkHtml();
        $html .= $this->getChooserContainerHtml();
        return $html;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption([
            '!='  => __('Do not empty category'),
            '='  => __('Empty category')
        ]);
        return $this;
    }

    public function processOnCollection(ProductCollection $collection)
    {
        // Empty Categories
        $this->_emptyCategories($collection);

        // Fill all attribute
        $this->_fillCategories($collection);
    }

    protected function _emptyCategories(ProductCollection $collection)
    {
        $ids = $this->_getCategoryIds();
        if ($this->getOperator() === '=' && count($ids) > 0) {
            $adapter = $collection->getResource()->getConnection();

            $cond = [
                'category_id IN (?)' => $ids
            ];
            $adapter->delete($collection->getTable('catalog_category_product'), $cond);
        }
    }

    protected function _fillCategories(ProductCollection $collection)
    {
        $ids = $this->_getCategoryIds();
        if (count($ids) > 0) {
            $adapter = $collection->getResource()->getConnection();
            $select = $adapter->select();
            $tableName = $collection->getTable('catalog_category_product');

            $allReadyIn = array();
            $maxPosition = 0;
            if ($this->getOperator() === '!=') {
                $lines = $select->reset()
                    ->from($tableName)
                    ->where('category_id IN ('.implode(',', $this->_getCategoryIds()).')')
                    ->query()
                    ->fetchAll();

                if (is_array($lines)) {
                    foreach ($lines as $line) {
                        if (!empty($line['product_id'])) {
                            $allReadyIn[$line['product_id']] = 1;
                            if ($line['position'] > $maxPosition) {
                                $maxPosition = $line['position'];
                            }
                        }
                    }
                }
            }

            foreach ($this->_getCategoryIds() as $categoryId) {
                $position = 1;
                $data = array();
                foreach ($collection->getItems() as $product) {
                    if (!isset($allReadyIn[$product->getId()])) {
                        $data[] = array(
                            'category_id' => (int)$categoryId,
                            'product_id' => (int)$product->getId(),
                            'position' => $position + $maxPosition
                        );

                        $position++;
                    }

                    // Save every 500 data
                    if (count($data) > 0 && $position % 500 == 0) {
                        $adapter->insertMultiple($tableName, $data);
                        $data = array();
                    }
                }
                if (count($data) > 0) {
                    $adapter->insertMultiple($tableName, $data);
                }
            }
        }
    }
}
