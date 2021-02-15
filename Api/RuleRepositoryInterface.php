<?php
namespace Sga\ProductRules\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Sga\ProductRules\Api\Data\RuleInterface as ModelInterface;

interface RuleRepositoryInterface
{
    public function save(ModelInterface $model);

    public function getById($id);

    public function getList(SearchCriteriaInterface $searchCriteria);

    public function delete(ModelInterface $model);

    public function deleteById($id);
}
