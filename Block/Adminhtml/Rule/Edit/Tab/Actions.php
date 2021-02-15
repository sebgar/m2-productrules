<?php
namespace Sga\ProductRules\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset as FieldsetRenderer;
use Magento\Rule\Block\Actions as BlockActions;
use Magento\Rule\Model\Action\AbstractAction;
use Sga\ProductRules\Model\RuleFactory as ModelFactory;

class Actions extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    protected $_rendererFieldset;
    protected $_actions;
    protected $_nameInLayout = 'actions_field';
    protected $_modelFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        BlockActions $actions,
        FieldsetRenderer $rendererFieldset,
        ModelFactory $modelFactory,
        array $data = []
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_actions = $actions;
        $this->_modelFactory = $modelFactory;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabClass()
    {
        return null;
    }

    public function getTabUrl()
    {
        return null;
    }

    public function isAjaxLoaded()
    {
        return false;
    }

    public function getTabLabel()
    {
        return __('Actions');
    }

    public function getTabTitle()
    {
        return __('Actions');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $id = $this->getRequest()->getParam('rule_id');
        $model = $this->_modelFactory->create();
        if ($id) {
            $model->load($id);
        }
        $form = $this->addTabToForm($model);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function addTabToForm($model, $fieldsetId = 'actions_fieldset', $formName = 'productrules_rule_form')
    {
        $actionsFieldSetId = $model->getActionsFieldSetId($formName);
        $newChildUrl = $this->getUrl(
            'productrules/rule/newActionHtml/form/' . $actionsFieldSetId,
            ['form_namespace' => $formName]
        );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $renderer = $this->_rendererFieldset
            ->setTemplate('Sga_ProductRules::rule/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($actionsFieldSetId);

        $urlAttributesConfig = $this->getUrl('adminhtml/system_config/edit', ['section' => 'productrules']);
        $fieldset = $form->addFieldset(
            $fieldsetId,
            [
                'legend' => __('You can parameter attribute to use in the section %1Attributes > For actions%2', '<a href="'.$urlAttributesConfig.'" target="_blank">', '</a>')
            ])
            ->setRenderer($renderer);

        $fieldset->addField(
            'actions',
            'text',
            [
                'name'           => 'actions',
                'label'          => __('Actions'),
                'title'          => __('Actions'),
                'required'       => true,
                'data-form-part' => $formName
            ])
            ->setRule($model)
            ->setRenderer($this->_actions);

        $form->setValues($model->getData());
        $this->setActionFormName($model->getActions(), $formName);
        return $form;
    }

    private function setActionFormName(AbstractAction $actions, $formName)
    {
        $actions->setFormName($formName);
        if ($actions->getActions() && is_array($actions->getActions())) {
            foreach ($actions->getActions() as $action) {
                $this->setActionFormName($action, $formName);
            }
        }
    }
}
