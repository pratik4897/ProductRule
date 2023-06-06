<?php
/**
 * 
 * Apoyar
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Apoyar
 * @package    Apoyar_ProductRules
 * @copyright  Copyright (c) 2023 Apoyar (http://www.apoyar.eu/)
 */

namespace Apoyar\ProductRules\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Apoyar\ProductRules\Model\RuleFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Psr\Log\LoggerInterface;
use Apoyar\ProductRules\Helper\Data;

abstract class Rule extends \Magento\Backend\App\Action
{
    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * @var \Apoyar\ProductRules\Model\RuleFactory
     */
    protected $ruleFactory;

    protected $resultRawFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    protected $helperData;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param Date $dateFilter
     * @param RuleFactory $ruleFactory
     * @param RawFactory $resultRawFactory
     * @param LoggerInterface $logger
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        Date $dateFilter,
        RuleFactory $ruleFactory,
        RawFactory $resultRawFactory,
        LoggerInterface $logger,
        Data $helperData
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->fileFactory = $fileFactory;
        $this->dateFilter = $dateFilter;
        $this->ruleFactory = $ruleFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->logger = $logger;
        $this->helperData = $helperData;
    }

    /**
     * Initiate rule
     *
     * @return void
     */
    protected function _initRule()
    {
        if (!$this->helperData->isEnabled()) {
            return;
        }
        $rule = $this->ruleFactory->create();
        $this->coreRegistry->register(
            'current_rule',
            $rule
        );
        $id = (int)$this->getRequest()->getParam('id');

        if (!$id && $this->getRequest()->getParam('rule_id')) {
            $id = (int)$this->getRequest()->getParam('rule_id');
        }

        if ($id) {
            $this->coreRegistry->registry('current_rule')->load($id);
        }
    }

    /**
     * Initiate action
     *
     * @return Rule
     */
    protected function _initAction()
    {
        if (!$this->helperData->isEnabled()) {
            return $this;
        }
        $this->_view->loadLayout();
        $this->_setActiveMenu('Apoyar_ProductRules::apoyar_productrules')
            ->_addBreadcrumb(__('Product Rules'), __('Product Rules'));
        return $this;
    }

    /**
     * Returns result of current user permission check on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        if (!$this->helperData->isEnabled()) {
            return false;
        }
        return $this->_authorization->isAllowed('Apoyar_ProductRules::config');
    }
}
