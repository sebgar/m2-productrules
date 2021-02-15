<?php
namespace Sga\ProductRules\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset as FieldsetRenderer;
use Magento\Rule\Block\Conditions as BlockConditions;
use Magento\Rule\Model\Condition\AbstractCondition;
use Sga\ProductRules\Model\RuleFactory as ModelFactory;

class Conditions extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    protected $_rendererFieldset;
    protected $_conditions;
    protected $_nameInLayout = 'conditions_field';
    protected $_modelFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        BlockConditions $conditions,
        FieldsetRenderer $rendererFieldset,
        ModelFactory $modelFactory,
        array $data = []
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions = $conditions;
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
        return __('Conditions');
    }

    public function getTabTitle()
    {
        return __('Conditions');
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

    protected function addTabToForm($model, $fieldsetId = 'conditions_fieldset', $formName = 'productrules_rule_form')
    {
        $conditionsFieldSetId = $model->getConditionsFieldSetId($formName);
        $newChildUrl = $this->getUrl(
            'productrules/rule/newConditionHtml/form/' . $conditionsFieldSetId,
            ['form_namespace' => $formName]
        );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $renderer = $this->_rendererFieldset
            ->setTemplate('Sga_ProductRules::rule/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($conditionsFieldSetId);

        $urlAttributesConfig = $this->getUrl('adminhtml/system_config/edit', ['section' => 'productrules']);
        $fieldset = $form->addFieldset(
            $fieldsetId,
            [
                'legend' => __('You can parameter attribute to use in the section %1Attributes > For conditions%2', '<a href="'.$urlAttributesConfig.'" target="_blank">', '</a>')
            ])
            ->setRenderer($renderer);

        $fieldset->addField(
            'conditions',
            'text',
            [
                'name'           => 'conditions',
                'label'          => __('Conditions'),
                'title'          => __('Conditions'),
                'required'       => true,
                'data-form-part' => $formName
            ])
            ->setRule($model)
            ->setRenderer($this->_conditions);

        $form->setValues($model->getData());
        $this->setConditionFormName($model->getConditions(), $formName);
        return $form;
    }

    private function setConditionFormName(AbstractCondition $conditions, $formName)
    {
        $conditions->setFormName($formName);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }
}
