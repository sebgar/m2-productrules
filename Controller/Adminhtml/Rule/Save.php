<?php
namespace Sga\ProductRules\Controller\Adminhtml\Rule;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Sga\ProductRules\Controller\Adminhtml\Rule as ParentClass;

class Save extends ParentClass implements HttpPostActionInterface
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = 1;
            }
            if (empty($data['rule_id'])) {
                $data['rule_id'] = null;
            }

            if (isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];
                if (isset($data['conditions_serialized'])) {
                    unset($data['conditions_serialized']);
                }
            }
            if (isset($data['rule']['actions'])) {
                $data['actions'] = $data['rule']['actions'];
                if (isset($data['actions_serialized'])) {
                    unset($data['actions_serialized']);
                }
            }
            unset($data['rule']);

            $model = $this->_modelFactory->create();

            $id = $this->getRequest()->getParam('rule_id');
            if ($id) {
                try {
                    $model = $this->_modelRepository->getById($id);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }

            $model->setData($data);
            $model->loadPost($data);

            try {
                $this->_modelRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the rule.'));
                $this->_dataPersistor->clear('productrules_rule');
                return $this->processReturn($model, $data, $resultRedirect);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the rule.'));
            }

            $this->_dataPersistor->set('productrules_rule', $data);
            return $resultRedirect->setPath('*/*/edit', ['rule_id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    private function processReturn($model, $data, $resultRedirect)
    {
        $redirect = $data['back'] ?? 'close';

        if ($redirect ==='continue') {
            $resultRedirect->setPath('*/*/edit', ['rule_id' => $model->getId()]);
        } else if ($redirect === 'close') {
            $resultRedirect->setPath('*/*/');
        } else if ($redirect === 'duplicate') {
            $duplicateModel = $this->_modelFactory->create(['data' => $data]);
            $duplicateModel->setId(null);
            $duplicateModel->setIsActive(0);
            $this->_modelRepository->save($duplicateModel);

            $id = $duplicateModel->getId();
            $this->messageManager->addSuccessMessage(__('You duplicated the rule.'));
            $this->_dataPersistor->set('productrules_rule', $data);
            $resultRedirect->setPath('*/*/edit', ['rule_id' => $id]);
        }
        return $resultRedirect;
    }
}
