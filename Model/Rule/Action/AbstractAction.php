<?php
namespace Sga\ProductRules\Model\Rule\Action;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Rule\Model\Action\AbstractAction as ParentAbstractAction;
use Magento\Rule\Model\ActionFactory;
use Magento\Catalog\Model\Product\Attribute\Repository as ProductAttributeRepository;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Sga\ProductRules\Helper\Config as ConfigHelper;
use Sga\ProductRules\Helper\Data as DataHelper;
use Sga\ProductRules\Model\Rule\ActionInterface;

abstract class AbstractAction extends ParentAbstractAction implements ActionInterface
{
    protected $_actionFactory;
    protected $_dateTime;
    protected $_localeDate;
    protected $_urlBuilder;
    protected $_searchCriteriaBuilder;
    protected $_productAttributeRepository;
    protected $_productFactory;
    protected $_productCollectionFactory;
    protected $_configHelper;
    protected $_dataHelper;

    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\LayoutInterface $layout,
        ActionFactory $actionFactory,
        TimezoneInterface $localeDate,
        DateTime $dateTime,
        UrlInterface $urlBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductAttributeRepository $productAttributeRepository,
        ProductFactory $productFactory,
        ProductCollectionFactory $productCollectionFactory,
        ConfigHelper $configHelper,
        DataHelper $dataHelper,
        array $data = [])
    {
        $this->_actionFactory = $actionFactory;
        $this->_dateTime = $dateTime;
        $this->_localeDate = $localeDate;
        $this->_urlBuilder = $urlBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_productAttributeRepository = $productAttributeRepository;
        $this->_productFactory = $productFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_configHelper = $configHelper;
        $this->_dataHelper = $dataHelper;

        parent::__construct($assetRepo, $layout, $data);

        $this->_construct();
    }

    protected function _construct()
    {

    }

    public function getTypeElement()
    {
        return $this->getForm()->addField(
            ///// BEGIN OLD
            //'action:' . $this->getId() . ':type',
            ///// END OLD
            ///// BEGIN SGA
            'action__' . $this->getId() . '__type',
            ///// END SGA
            'hidden',
            [
                'name' => $this->elementName . '[actions][' . $this->getId() . '][type]',
                'value' => $this->getType(),
                ///// BEGIN SGA
                'class' => 'hidden',
                'data-form-part' => $this->getFormName(),
                ///// END SGA
                'no_span' => true
            ]
        );
    }

    public function getAttributeElement()
    {
        return $this->getForm()
            ->addField(
                ///// BEGIN OLD
                //'action:' . $this->getId() . ':attribute',
                ///// END OLD
                ///// BEGIN SGA
                'action__' . $this->getId() . '__attribute',
                ///// END SGA
                'select',
                [
                    'name' => $this->elementName . '[actions][' . $this->getId() . '][attribute]',
                    'values' => $this->getAttributeSelectOptions(),
                    'value' => $this->getAttribute(),
                    'value_name' => $this->getAttributeName(),
                    ///// BEGIN SGA
                    'data-form-part' => $this->getFormName()
                    ///// END SGA
                ]
            )
            ->setRenderer($this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class));
    }

    public function getOperatorElement()
    {
        return $this->getForm()
            ->addField(
                ///// BEGIN OLD
                //'action:' . $this->getId() . ':operator',
                ///// END OLD
                ///// BEGIN SGA
                'action__' . $this->getId() . '__operator',
                ///// END SGA
                'select',
                [
                    'name' => $this->elementName . '[actions][' . $this->getId() . '][operator]',
                    'values' => $this->getOperatorSelectOptions(),
                    'value' => $this->getOperator(),
                    'value_name' => $this->getOperatorName(),
                    ///// BEGIN SGA
                    'data-form-part' => $this->getFormName()
                    ///// END SGA
                ]
            )
            ->setRenderer($this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class));
    }

    public function getValueElement()
    {
        ///// BEGIN OLD
        /*return $this->getForm()->addField(
            'action:' . $this->getId() . ':value',
            'text',
            [
                'name' => $this->elementName . '[actions][' . $this->getId() . '][value]',
                'value' => $this->getValue(),
                'value_name' => $this->getValueName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class)
        );*/
        ///// END OLD
        ///// BEGIN SGA
        $elementParams = [
            'name' => $this->elementName . '[actions][' . $this->getId() . '][value]',
            'value' => $this->getValue(),
            'values' => $this->getValueSelectOptions(),
            'value_name' => $this->getValueName(),
            'after_element_html' => $this->getValueAfterElementHtml(),
            'explicit_apply' => $this->getExplicitApply(),
            'data-form-part' => $this->getFormName()
        ];
        if ($this->getInputType() == 'date') {
            // date format intentionally hard-coded
            $elementParams['input_format'] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
            $elementParams['date_format'] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
            $elementParams['placeholder'] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
            $elementParams['autocomplete'] = 'off';
            $elementParams['readonly'] = 'true';
            $elementParams['value_name'] =
                (new \DateTime($elementParams['value'], new \DateTimeZone($this->_localeDate->getConfigTimezone())))
                    ->format('Y-m-d');
        }
        return $this->getForm()
            ->addField(
                'action__' . $this->getId() . '__value',
                $this->getValueElementType(),
                $elementParams
            )
            ->setRenderer($this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class));
        ///// END SGA
    }

    public function getValueName()
    {
        $value = $this->getValue();
        if (is_null($value) || '' === $value) {
            return '...';
        }

        $options = $this->getValueSelectOptions();
        $valueArr = [];
        if (!empty($options)) {
            foreach ($options as $o) {
                if (is_array($value)) {
                    if (in_array($o['value'], $value)) {
                        $valueArr[] = $o['label'];
                    }
                } else {
                    if (is_array($o['value'])) {
                        foreach ($o['value'] as $v) {
                            if ($v['value']==$value) {
                                return $v['label'];
                            }
                        }
                    }
                    if ($o['value'] == $value) {
                        return $o['label'];
                    }
                }
            }
        }
        if (!empty($valueArr)) {
            $value = implode(', ', $valueArr);
        }
        return $value;
    }

    protected function _getStoreId()
    {
        return $this->getRule()->getStoreId();
    }
}
