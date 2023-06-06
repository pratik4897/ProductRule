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

namespace Apoyar\ProductRules\Model;

use Magento\Framework\App\ResourceConnection;
use Apoyar\ProductRules\Model\Rule;
use Apoyar\ProductRules\Model\RuleFactory;
use Apoyar\ProductRules\Helper\Data as RuleHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Indexer\Model\IndexerFactory;
use Apoyar\ProductRules\Model\ResourceModel\Items;

class Cron
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $_resource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    protected $rules;

    protected $ruleFactory;

    protected $ruleHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $indexerFactory;
    protected $itemModel;

    /**
     * Recipient email config path
     */
    const XML_SPECIFIC_IDS = 'productruletab/generalRule/ruleSpecificIds';

    /**
     *
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resource
     * @param DateTime $dateTime
     * @param Rule $rule
     * @param RuleFactory $ruleFactory
     * @param RuleHelper $ruleHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param IndexerFactory $indexerFactory
     * @param Items $itemModel
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        DateTime $dateTime,
        Rule $rule,
        RuleFactory $ruleFactory,
        RuleHelper $ruleHelper,
        ScopeConfigInterface $scopeConfig,
        IndexerFactory $indexerFactory,
        Items $itemModel
    ) {
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
        $this->_dateTime = $dateTime;
        $this->rules = $rule;
        $this->ruleFactory = $ruleFactory;
        $this->ruleHelper = $ruleHelper;
        $this->scopeConfig = $scopeConfig;
        $this->indexerFactory = $indexerFactory;
        $this->itemModel = $itemModel;
    }

    /**
     * Retrieve write connection instance
     *
     * @return bool|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getConnection()
    {
        if (null === $this->_connection) {
            $this->_connection = $this->_resource->getConnection();
        }
        return $this->_connection;
    }

     /**
     * cron execute functions
     */
    public function execute()
    {
        if (!$this->ruleHelper->isEnabled()) {
            return;
        }
        $slot = 2;
        $rules = $this->getAllActiveRules($slot);
        foreach ($rules as $rule) {
            $this->ruleHelper->processRule($rule);
        }
        //reindex category flat
        $this->reindexCategoriesItems();
    }

    /**
     * get All the active rules sort by lowest to highest
     */
    public function getAllActiveRules($slot)
    {
        $ruleIds = $this->itemModel->getAllEnabledRules($slot);
        if (count($ruleIds)> 0) {
            $model = $this->ruleFactory->create();
            $collection = $model->getCollection();
            $collection->addFieldToFilter('rule_id', ["in" => $ruleIds]);
            $collection->addFieldToFilter('is_active', ["eq" => 1]);
            $collection->setOrder('sort_order', 'ASC');
            return $collection;
        }
    }

    /**
     * Run Specific rules by cron which configured in backend
     */
    public function runRules()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $ruleIds = $this->scopeConfig->getValue(self::XML_SPECIFIC_IDS, $storeScope);
        if (!empty($ruleIds)) {
            $now = new \DateTime();
            $model = $this->ruleFactory->create();
            $collection = $model->getCollection();
            $collection->addFieldToFilter('rule_id', ["in" => $ruleIds]);
            $collection->addFieldToFilter('is_active', ["eq" => 1]);
            $collection->addFieldToFilter('is_dry_run', ["eq" => 0]);
            $collection->addFieldToFilter('from_date', [['lteq' => $now->format('Y-m-d')],['null'=>true]]);
            $collection->addFieldToFilter('to_date', [['gteq' => $now->format('Y-m-d')],['null'=>true]]);
            $collection->setOrder('sort_order', 'ASC');
            $collection;
            foreach ($collection as $rule) {
                $this->ruleHelper->processRule($rule);
            }
        }
    }   
    
    /**
     * Reindex Categories after cron run
     *
     * @return void
     */
    public function reindexCategoriesItems()
    {
        $indexerIds = [
            'catalog_category_product',
            'catalog_product_category'
        ];
        foreach ($indexerIds as $indexerId) {
            $indexer = $this->indexerFactory->create();
            $indexer->load($indexerId);
            $indexer->reindexAll();
        }
    }  

    /**
     * Execute cron Slot1 and process rules
     *
     * @return void
     */
    public function executeSlot1()
    {
        $slot = 1;
        $rules = $this->getAllActiveRules($slot);
        foreach ($rules as $rule) {
            $this->ruleHelper->processRuleSlot($rule);
        }
        //reindex category flat
        $this->reindexCategoriesItems();
    }
}
