<?php
namespace Sga\ProductRules\Block\Adminhtml\Rule\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ExecuteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getId()) {
            $data = [
                'label' => __('Execute'),
                'class' => 'save',
                'on_click' => 'deleteConfirm(\'' . __('Are you sure you want to execute ?') . '\', \'' . $this->getExecuteUrl() . '\', {"data": {}})',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getExecuteUrl()
    {
        return $this->getUrl('*/*/execute', ['rule_id' => $this->getId()]);
    }
}
