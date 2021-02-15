<?php
namespace Sga\ProductRules\Ui\Component\Rule;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    protected $_authorization;
    protected $_additionalFilterPool;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        AuthorizationInterface $authorization,
        array $meta = [],
        array $data = [],
        array $additionalFilterPool = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );

        $this->_authorization = $authorization;
        $this->meta = array_replace_recursive($meta, $this->prepareMetadata());
        $this->_additionalFilterPool = $additionalFilterPool;
    }

    public function prepareMetadata()
    {
        $metadata = [];

        if (!$this->_authorization->isAllowed('Sga_ProductRules::productrules_rule')) {
            $metadata = [
                'productrules_rule_columns' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'editorConfig' => [
                                    'enabled' => false
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        return $metadata;
    }

    public function addFilter(Filter $filter)
    {
        if (!empty($this->_additionalFilterPool[$filter->getField()])) {
            $this->_additionalFilterPool[$filter->getField()]->addFilter($this->searchCriteriaBuilder, $filter);
        } else {
            parent::addFilter($filter);
        }
    }
}
