<?php
namespace Sga\ProductRules\Controller\Adminhtml\Rule;

use Sga\ProductRules\Controller\Adminhtml\Rule as ParentClass;

class Index extends ParentClass
{
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $this->initPage($resultPage);

        return $resultPage;
    }
}
