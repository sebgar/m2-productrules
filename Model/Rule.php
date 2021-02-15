<?php
namespace Sga\ProductRules\Model;

use Psr\Log\LoggerInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Rule\Model\AbstractModel;
use Sga\ProductRules\Api\Data\RuleInterface as ModelInterface;
use Sga\ProductRules\Helper\Data as DataHelper;
use Sga\ProductRules\Model\ResourceModel\Rule as ResourceModel;
use Sga\ProductRules\Model\Rule\Condition\CombineFactory;
use Sga\ProductRules\Model\Rule\Action\CollectionFactory as ActionCollectionFactory;
use Sga\ProductRules\Model\ResourceModel\Product\Collection as ProductCollection;
use Sga\ProductRules\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class Rule extends AbstractModel implements IdentityInterface, ModelInterface
{
    const CACHE_TAG = 'productrules_rule';

    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_ERROR = 'error';
    const STATUS_SUCCESS = 'success';

    protected $_nbTreatedByIteration = 100;
    protected $_storeId = 0;

    protected $_eventPrefix = 'productrules_rule';

    protected $_logger;
    protected $_combineFactory;
    protected $_actionCollectionFactory;
    protected $_dataHelper;
    protected $_dateTime;
    protected $_eventManager;
    protected $_productCollectionFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Psr\Log\LoggerInterface $logger,
        CombineFactory $combineFactory,
        ActionCollectionFactory $actionCollectionFactory,
        DataHelper $dataHelper,
        DateTime $dateTime,
        EventManagerInterface $eventManager,
        ProductCollectionFactory $productCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        array $data = []
    ){
        $this->_combineFactory = $combineFactory;
        $this->_actionCollectionFactory = $actionCollectionFactory;
        $this->_dataHelper = $dataHelper;
        $this->_dateTime = $dateTime;
        $this->_eventManager = $eventManager;
        $this->_productCollectionFactory = $productCollectionFactory;

        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data, $extensionFactory, $customAttributeFactory, $serializer);

        // set it after otherwize, it will be rewrite by parent construct
        $this->_logger = $logger;
    }

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getRuleId()
    {
        return $this->getData(self::RULE_ID);
    }

    public function setRuleId($id)
    {
        return $this->setData(self::RULE_ID, $id);
    }

    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    public function setCode($value)
    {
        return $this->setData(self::CODE, $value);
    }

    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    public function setIsActive($value)
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    public function setTitle($value)
    {
        return $this->setData(self::TITLE, $value);
    }

    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    public function setDescription($value)
    {
        return $this->setData(self::DESCRIPTION, $value);
    }

    public function getMemoryLimit()
    {
        return $this->getData(self::MEMORY_LIMIT);
    }

    public function setMemoryLimit($value)
    {
        return $this->setData(self::MEMORY_LIMIT, $value);
    }

    public function getStoreIds()
    {
        return $this->getData(self::STORE_IDS);
    }

    public function setStoreIds($value)
    {
        return $this->setData(self::STORE_IDS, $value);
    }

    public function getProductLimit()
    {
        return $this->getData(self::PRODUCT_LIMIT);
    }

    public function setProductLimit($value)
    {
        return $this->setData(self::PRODUCT_LIMIT, $value);
    }

    public function getConditionsSerialized()
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }

    public function setConditionsSerialized($value)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $value);
    }

    public function getActionsSerialized()
    {
        return $this->getData(self::ACTIONS_SERIALIZED);
    }

    public function setActionsSerialized($value)
    {
        return $this->setData(self::ACTIONS_SERIALIZED, $value);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt($value)
    {
        return $this->setData(self::CREATED_AT, $value);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function setUpdatedAt($value)
    {
        return $this->setData(self::UPDATED_AT, $value);
    }

    public function getStartedAt()
    {
        return $this->getData(self::STARTED_AT);
    }

    public function setStartedAt($value)
    {
        return $this->setData(self::STARTED_AT, $value);
    }

    public function getFinishedAt()
    {
        return $this->getData(self::FINISHED_AT);
    }

    public function setFinishedAt($value)
    {
        return $this->setData(self::FINISHED_AT, $value);
    }

    public function getConditionsInstance()
    {
        return $this->_combineFactory->create();
    }

    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    public function getActionsInstance()
    {
        return $this->_actionCollectionFactory->create();
    }

    public function getActionsFieldSetId($formName = '')
    {
        return $formName . 'rule_actions_fieldset_' . $this->getId();
    }

    public function computeCollectionFilters()
    {
        $filters = $this->getConditions()->computeCollectionFilters();
        $where = $this->_dataHelper->convertFiltersClause($filters);
        if (count($where) > 0) {
            $this->getProductCollection()->getSelect()->where(implode(' AND ', $where));
        }

        return $this->getProductCollection();
    }

    public function computeCollectionActions(ProductCollection $collection)
    {
        $this->getActions()->processOnCollection($collection);
        return $this;
    }

    public function executeProductRules($id = null)
    {
        if ($id !== null) {
            $this->load($id);
            if ($this->getId() != $id) {
                throw new \Exception(__('ProductRules #%1 does not exists !', $id));
            }
        }

        $this->setStatus(self::STATUS_RUNNING)
            ->setStartedAt($this->_dateTime->gmtDate())
            ->save();

        try {
            $memoryLimit = $this->getMemoryLimit();
            if ($memoryLimit) {
                if (ini_set('memory_limit', $memoryLimit) === false) {
                    throw new \Exception(__('Invalid memory limit value "%1".', $memoryLimit));
                }
            }

            $this->_registry->unregister('current_productrules');
            $this->_registry->register('current_productrules', $this);

            $storeIds = is_array($this->getStoreIds()) ? $this->getStoreIds() : explode(',', $this->getStoreIds());
            if (is_array($storeIds)) {
                foreach ($storeIds as $storeId) {
                    // set store id
                    $this->setStoreId($storeId);

                    // execute normal
                    $this->_executeProductRules($this->getStoreId());
                }
            }

            $this->setStatus(self::STATUS_SUCCESS)
                ->setFinishedAt($this->_dateTime->gmtDate())
                ->setStatusMessage('OK')
                ->save();

        } catch (\Exception $e) {
            $this->setStatus(self::STATUS_ERROR)
                ->setFinishedAt($this->_dateTime->gmtDate())
                ->setStatusMessage($e->getMessage())
                ->save();

            throw $e;
        }
    }

    protected function _executeProductRules($storeId)
    {
        $this->_logger->info('Execute productrules "'.$this->getCode().'" #'.$this->getId().', store '.$storeId);

        $this->_conditions = null;
        $this->_actions = null;

        $action = $this->getActions();
        $actions = $action->getData($action->getPrefix());
        $hasActions = count($actions) > 0 ? true : false;

        if ($hasActions) {
            /* @var $collection \Sga\ProductRules\Model\ResourceModel\Product\Collection */
            $collection = $this->_productCollectionFactory->create()
                ->addAttributeToSelect('sku');

            // Add store
            $collection->setStoreId($storeId);

            // Add limit
            $limit = (int)$this->getProductLimit();
            if ($limit > 0) {
                $collection->getSelect()->limit($limit);
            }

            // Execute rule conditions
            $this->setProductCollection($collection);
            $collection = $this->computeCollectionFilters();

            /* @var $select Zend_Db_Select */
            $select = clone $collection->getSelect();
            $lines = $select->reset(\Zend_Db_Select::COLUMNS)
                ->reset(\Zend_Db_Select::ORDER)
                ->columns('e.entity_id')
                ->query()
                ->fetchAll();

            $max = count($lines);
            if ($limit > 0 && $max >= $limit) {
                $max = $limit;
            }
            $nbIteration = floor($max / $this->_nbTreatedByIteration);

            // Execute actions on collection
            for ($i = $nbIteration; $i >= 0; $i--) {
                // process iteration in order inverse
                if ($i == $nbIteration) {
                    $l = $max - ($this->_nbTreatedByIteration * $i);
                } else {
                    $l = $this->_nbTreatedByIteration;
                }

                // clear and change limit
                $collection->clear();
                $collection->getSelect()->limit($l, $this->_nbTreatedByIteration * $i);

                // log query
                $this->_logger->info('  '.$collection->load()->getSelect()->assemble());

                // execute actions
                $count = count($collection->getItems());
                if ($count > 0) {
                    $this->_logger->info('  found '.$count.' product(s)');

                    // Execute actions on collection
                    $this->computeCollectionActions($collection);
                } else {
                    $this->_logger->info('  no product found');
                }

                // dispatch event
                $this->_eventManager->dispatch('productproductrules_execute_after', ['collection' => $collection, 'store_id' => $storeId]);
            }
        } else {
            $this->_logger->info('  no actions found');
        }
    }
}
