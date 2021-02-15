<?php
namespace Sga\ProductRules\Model\Rule\Condition\Product;

use Sga\ProductRules\Model\Rule\Condition\Product;
use Sga\ProductRules\Model\Rule\ConditionInterface;
use Sga\ProductRules\Model\ResourceModel\Product\Collection as ProductCollection;

class InWebsite extends Product implements ConditionInterface
{
    protected $_websites;

    public function loadArray($arr)
    {
        $this->setValue('');

        parent::loadArray($arr);
        $this->setWebsite(isset($arr['website']) ? $arr['website'] : array());
        return $this;
    }

    public function asArray(array $arrAttributes = array())
    {
    	$out = parent::asArray($arrAttributes);
    	$out['website'] = $this->getWebsite();
    	return $out;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            'in' => __('is in'),
            'nin' => __('is not in'),
        ));
        return $this;
    }

    public function getWebsiteElement()
    {
        $websiteSelected = $this->getWebsiteSelected();

        return $this->getForm()->addField($this->getPrefix().'__'.$this->getId().'__website', 'multiselect', array(
            'name' => 'rule['.$this->getPrefix().']['.$this->getId().'][website][]',
            'values' => $this->getWebsiteOptions(),
            'value' => (is_array($websiteSelected) ? implode(',', $websiteSelected) : $websiteSelected),
            'value_name' => $this->getWebsiteName(),
            'data-form-part' => $this->getFormName()
        ))->setRenderer($this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class));
    }

    public function getWebsiteOptions()
    {
        if (!isset($this->_websites)) {
            $websites = $this->_storeManager->getWebsites();
            foreach ($websites as $website) {
                $this->_websites[$website->getId()] = array(
                    'value' => $website->getId(),
                    'label' => $website->getName()
                );
            }
        }
        return $this->_websites;
    }

    public function getWebsiteSelected()
    {
        $websites = $this->getWebsiteOptions();

        $websiteSelected = $this->getData('website');
        if (!isset($websiteSelected)) {
            foreach ($websites as $label) {
                $websiteSelected[] = $label['value'];
                break;
            }
        } elseif (is_string($websiteSelected)) {
            $websiteSelected = (array)$websiteSelected;
        }

        return $websiteSelected;
    }

    public function getWebsiteName()
    {
        $websites = $this->getWebsiteOptions();
        $websiteSelected = $this->getWebsiteSelected();

        $labels = array();
        foreach ($websites as $label) {
            if (in_array($label['value'], $websiteSelected)) {
                $labels[] = $label['label'];
            }
        }

        return implode(',', $labels);
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml();

        $html .= __("Product %1 website(s) %2",
            $this->getOperatorElement()->getHtml(),
            $this->getWebsiteElement()->getHtml()
        );

        if ($this->getId() != '1') {
            $html.= $this->getRemoveLinkHtml();
        }

        return $html;
    }

    public function computeCollectionFilters()
    {
        $this->_joinWebsiteCollection($this->getRule()->getProductCollection());
        return array();
    }

    protected function _joinWebsiteCollection(ProductCollection $collection)
    {
        $fromPart = $collection->getSelect()->getPart(\Zend_Db_Select::FROM);
        if (!isset($fromPart['cpw'])) {
            // Liaison product <=> websites
            $websites = $this->getWebsiteSelected();
            if (count($websites) > 0) {
                $conditions = array('e.entity_id=cpw.product_id');
                switch($this->getOperator()) {
                    case 'in':
                        $conditions[] = 'cpw.website_id IN ('.implode(',', $websites).')';
                    break;
                    case 'nin':
                        $conditions[] = 'cpw.website_id NOT IN ('.implode(',', $websites).')';
                    break;
                }

                $collection->getSelect()->join(
                    array('cpw' => $collection->getTable('catalog_product_website')),
                    join(' AND ', $conditions),
                    null
                );

                $collection->getSelect()->columns('GROUP_CONCAT(DISTINCT cpw.website_id) as website_ids');
                $collection->getSelect()->group('e.entity_id');
            }
        }
    }
}
