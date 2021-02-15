<?php
namespace Sga\ProductRules\Model\System\Config\Source\Product;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\Product\Attribute\Repository as ProductAttributeRepository;

class Attributes
{
    protected $_searchCriteriaBuilder;
    protected $_productAttributeRepository;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductAttributeRepository $productAttributeRepository
    ) {
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_productAttributeRepository = $productAttributeRepository;
    }

    protected function _getOptions()
    {
        $searchCriteria = $this->_searchCriteriaBuilder->create();
        return $this->_productAttributeRepository->getList($searchCriteria)->getItems();
    }

    public function toOption()
    {
        $options = $this->_getOptions();

        $lines = [];
        foreach ($options as $option) {
            $lines[$option->getAttributeCode()] = $option->getFrontendLabel();
        }
        return $lines;
    }

    public function toOptionArray()
    {
        $options = $this->_getOptions();

        $lines = [];
        foreach ($options as $option) {
            $lines[] = ['value' => $option->getAttributeCode(), 'label' => $option->getFrontendLabel()];
        }
        return $lines;
    }
}
