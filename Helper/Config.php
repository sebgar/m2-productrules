<?php
namespace Sga\ProductRules\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    protected $_scopeConfig;

    const XML_PATH_ATTRIBUTES_CONDITION = 'productrules/attributes/condition';
    const XML_PATH_ATTRIBUTES_ACTION = 'productrules/attributes/action';

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context
    ){
        $this->_scopeConfig = $scopeConfig;

        parent::__construct($context);
    }

    public function getAttributesCondition($store = null)
    {
        return explode(',', $this->_scopeConfig->getValue(
            self::XML_PATH_ATTRIBUTES_CONDITION,
            ScopeInterface::SCOPE_STORE,
            $store
        ));
    }

    public function getAttributesAction($store = null)
    {
        return explode(',', $this->_scopeConfig->getValue(
            self::XML_PATH_ATTRIBUTES_ACTION,
            ScopeInterface::SCOPE_STORE,
            $store
        ));
    }
}
