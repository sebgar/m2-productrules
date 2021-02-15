<?php
namespace Sga\ProductRules\Controller\Adminhtml\Rule;

use Magento\Framework\Controller\ResultFactory;
use Sga\ProductRules\Controller\Adminhtml\Rule as ParentClass;

class MassDisable extends ParentClass
{
    public function execute()
    {
        $collection = $this->_massActionFilter->getCollection($this->_collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $item) {
            $item->setIsActive(false)->save();
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been disabled.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
