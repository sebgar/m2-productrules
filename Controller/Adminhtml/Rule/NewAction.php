<?php
namespace Sga\ProductRules\Controller\Adminhtml\Rule;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Sga\ProductRules\Controller\Adminhtml\Rule as ParentClass;

class NewAction extends ParentClass implements HttpGetActionInterface
{
    public function execute()
    {
        $resultForward = $this->_resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
