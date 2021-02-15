<?php
namespace Sga\ProductRules\Model\Rule;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

interface ActionInterface
{
    public function processOnCollection(ProductCollection $collection);
}
