<?php
namespace Sga\ProductRules\Controller\Adminhtml\Rule;

use Sga\ProductRules\Controller\Adminhtml\Rule as ParentClass;

class InlineEdit extends ParentClass
{
    public function execute()
    {
        $resultJson = $this->_jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $id) {
                    $model = $this->_modelRepository->getById($id);
                    try {
                        $model->setData(array_merge($model->getData(), $postItems[$id]));
                        $this->_modelRepository->save($model);
                    } catch (\Exception $e) {
                        $messages[] = '[ID: ' . $model->getId() . '] ' . __($e->getMessage());
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}
