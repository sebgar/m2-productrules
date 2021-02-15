<?php
namespace Sga\ProductRules\Controller\Adminhtml;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Ui\Component\MassAction\Filter as MassActionFilter;
use Sga\ProductRules\Model\RuleFactory as ModelFactory;
use Sga\ProductRules\Model\ResourceModel\Rule\CollectionFactory;
use Sga\ProductRules\Api\RuleRepositoryInterface as ModelRepository;

abstract class Rule extends Action
{
    const ADMIN_RESOURCE = 'Sga_ProductRules::rule';

    protected $_resultPageFactory;
    protected $_resultForwardFactory;
    protected $_jsonFactory;
    protected $_modelFactory;
    protected $_modelRepository;
    protected $_collectionFactory;
    protected $_massActionFilter;
    protected $_dataPersistor;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        JsonFactory $jsonFactory,
        ModelFactory $modelFactory,
        ModelRepository $modelRepository,
        CollectionFactory $collectionFactory,
        MassActionFilter $massActionFilter,
        DataPersistorInterface $dataPersistor
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultForwardFactory = $resultForwardFactory;
        $this->_jsonFactory = $jsonFactory;
        $this->_modelFactory = $modelFactory;
        $this->_modelRepository = $modelRepository;
        $this->_collectionFactory = $collectionFactory;
        $this->_massActionFilter = $massActionFilter;
        $this->_dataPersistor = $dataPersistor;

        parent::__construct($context);
    }

    protected function initPage(Page $resultPage)
    {
        $resultPage->setActiveMenu('Sga_ProductRules::productrules')
            ->addBreadcrumb(__('Product Rules'), __('Product Rules'));

        $resultPage->getConfig()->getTitle()->prepend(__('Product Rules'));

        return $resultPage;
    }
}
