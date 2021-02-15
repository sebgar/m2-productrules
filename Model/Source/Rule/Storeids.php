<?php
namespace Sga\ProductRules\Model\Source\Rule;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

class Storeids implements OptionSourceInterface
{
    protected $_storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ){
        $this->_storeManager = $storeManager;
    }

    public function getOptions()
    {
        $list = [
            0 => __('All Storeviews')
        ];

        foreach ($this->_storeManager->getStores() as $store) {
            $list[$store->getId()] = $store->getWebsite()->getName().' / '.$store->getName();
        }
        return $list;
    }

    public function toOptionArray()
    {
        $availableOptions = $this->getOptions();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    public function toArray()
    {
        return $this->getOptions();
    }
}
