<?php
namespace Sga\ProductRules\Model\ResourceModel\Rule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Sga\ProductRules\Model\Rule as Model;
use Sga\ProductRules\Model\ResourceModel\Rule as ResourceModel;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'rule_id';

    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);

    }

}
