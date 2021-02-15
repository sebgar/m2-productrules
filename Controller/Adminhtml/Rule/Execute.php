<?php
namespace Sga\ProductRules\Controller\Adminhtml\Rule;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Sga\ProductRules\Controller\Adminhtml\Rule as ParentClass;

class Execute extends ParentClass implements HttpPostActionInterface
{
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('rule_id');
        if ($id) {
            try {
                $model = $this->_modelFactory->create();
                $model->load($id);

                $timeStart = microtime(true);
                $model->executeProductRules();
                $timeEnd = microtime(true);

                $this->messageManager->addSuccessMessage(__('Rule executed successfully in %1 sec.', round($timeEnd - $timeStart, 3)));
                return $resultRedirect->setPath('*/*/edit', ['rule_id' => $id]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['rule_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a rule to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
