<?php
namespace Sga\ProductRules\Controller\Adminhtml\Rule;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Sga\ProductRules\Model\Rule as Model;
use Sga\ProductRules\Controller\Adminhtml\Rule as ParentClass;

class Edit extends ParentClass implements HttpGetActionInterface
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('rule_id');
        $model = $this->_modelFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));

                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $resultPage = $this->_resultPageFactory->create();
        $this->initPage($resultPage)
            ->addBreadcrumb(
            $id ? __('Edit Rule') : __('New Rule'),
            $id ? __('Edit Rule') : __('New Rule')
            );
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? 'Rule #'.$model->getId() : __('New Rule'));

        return $resultPage;
    }
}
