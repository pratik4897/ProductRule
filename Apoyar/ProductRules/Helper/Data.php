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

namespace Apoyar\ProductRules\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as productFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Apoyar\ProductRules\Helper\Logger;
use Apoyar\ProductRules\Model\ResourceModel\Items;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Context
     */
    protected $context;
    protected $resourceConnection;
    protected $action;
    protected $matches = [];
    protected $logViewFileName;
    protected $productCollectionFactory;
    protected $scopeConfig;
    protected $serializer;
    protected $logger;
    protected $itemModel;

    /**
     * Data constructor.
     * @param Context $context
     * @param productFactory $productCollectionFactory
     * @param ResourceConnection $resourceConnection
     * @param Action $action
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     * @param Logger $logger
     * @param Items $itemModel
     */

    public function __construct(
        Context $context,
        productFactory $productCollectionFactory,
        ResourceConnection $resourceConnection,
        Action $action,
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer,
        Logger $logger,
        Items $itemModel
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->context = $context;
        $this->action = $action;
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->itemModel = $itemModel;
    }

    /**
     * For checking if the module is enabled
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue('productruletab/generalRule/enable', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Process the rule slot 2
     * @param \Apoyar\ProductRules\Model\Rule $rule
     * @param number $testRun
     * @return string|boolean
     */
    public function processRule($rule, $testRun = 0)
    {
        $rule->setLastrunUpdates(0);
        $rule->save();
        $this->logViewFileName = $this->logger->syncLogFile($rule->getRuleId(), 'PRODUCT_RULES_SLOT_2');
        $txt = "Info : " . 'Execution started for rule:'.$rule->getName();
        $this->logger->writeLogToFile($this->logViewFileName, $txt);
        $this->getProductsBasedOnConditions($rule, $testRun);
        return $this->logViewFileName;
    }

    /**
     * function to process the slot 1 
     * @param $rule
     * @param int $testRun
     * @return string
     */
    public function processRuleSlot($rule, $testRun = 0)
    {
        $rule->setLastrunUpdates(0);
        $rule->save();
        $this->logViewFileName = $this->logger->syncLogFile($rule->getRuleId(), 'PRODUCT_RULES_SLOT_1');
        $txt = "Info : " . 'Execution started for rule:'.$rule->getName();
        $this->logger->writeLogToFile($this->logViewFileName, $txt);
        $this->getProductsBasedOnConditions($rule, $testRun);
        return $this->logViewFileName;
    }
 
    /**
     * get product list based on the rule dry run condtions
     * @param \Apoyar\ProductRules\Model\Rule $rule
     * @param boolean $testRun
     * @return boolean
     */
    public function getProductsBasedOnConditions($rule, $testRun)
    {
        $start = microtime(true);
        $products = $rule->getListProductIdsInRule($testRun, $this->logViewFileName);
        $finish = microtime(true);
        // Subtract the start time from the end time to get our difference in seconds
        $totaltime = $finish - $start;
        //update run time
        if ($testRun!=1 || $rule->getIsDryRun()) {
            $rule->setLastrunDuration($totaltime);
            $rule->setLastrunMatches(count($products));
            $rule->save();
        }
        if ($testRun ==1 || $rule->getIsDryRun() == 1) {
            foreach ($products as $sku) {
                $txt = "Product Sku ( Dry Run or Test run): $sku";
                $this->logger->writeLogToFile($this->logViewFileName, $txt);
            }
        }
        return true;
    }

    /**
     * @param $collection
     * @param $rule
     * @param $testrun
     */
    public function addProductsInLog($collection, $rule, $testrun)
    {
        if (count($collection) > 0) {
            $dryRun = $rule->getIsDryRun();
            $strDry = "";
            if ($dryRun == 1 || $testrun== 1) {
                $strDry = "not saved: dry run";
            }
            $dCategories = $rule->getCategories();
            $dUnassociateCategories = $rule->getUnassociateCategories();
            foreach ($collection as $p) {
                $txt = "Product SKU ($strDry) : " . $p->getSku();
                if ($dryRun == 1 || $testrun== 1) {
                    $this->logger->writeLogToFile($this->logViewFileName, $txt);
                }
                if ($dryRun == 0 && $testrun== 0) {
                    $this->updateProduct($p, $dCategories, $dUnassociateCategories, $rule);
                }
            }
        }
    }

    /**
     * Prepare the log for test run
     * @param object Product
     * @param \Apoyar\ProductRules\Model\Rule $rule
     * @param boolean $testrun
     */
    public function addProductInLog($product, $rule, $testrun, $logfile)
    {
        $this->logViewFileName = $logfile;
        $dryRun = $rule->getIsDryRun();
        $rules = $rule->getRules();
        $strDry = "";
        if ($dryRun == 1 || $testrun== 1) {
            $strDry = "not saved: dry run";
        }
        $dCategories = $rule->getCategories();
        $dUnassociateCategories = $rule->getUnassociateCategories();
        $txt = "Product SKU ($strDry) : " . $product->getSku();
        if ($dryRun == 1 || $testrun== 1) {
            $this->logger->writeLogToFile($this->logViewFileName, $txt);
        }
        if ($dryRun == 0 && $testrun== 0) {
            $this->updateProduct($product, $dCategories, $dUnassociateCategories, $rule);
        }
    }

    /**
     * Update product based on the action attributes in rule
     * @param mixed $product
     * @param string $categories
     * @param string $removeCategories
     * @param \Apoyar\ProductRules\Model\Rule $rule
     */
    public function updateProduct($product, $categories, $removeCategories, $rule)
    {
        $entityId = $product->getId();
        $rules = $rule->getRules();
        $unserialiseRules = unserialize($rules);
        $arrCategories = explode(",",(string)$categories);
        $match = 0;

        if (!empty($categories)) {
            $arrCategories = explode(",", $categories);
            foreach ($arrCategories as $c) {
                $exists = $this->fetchCategoryId($entityId, $c);
                if (!$exists) {
                    $this->assignCategoryToProduct($entityId, $c);
                    $txt = "Category ID $c Assigned to  : " . $product->getSku();
                    $this->logger->writeLogToFile($this->logViewFileName, $txt);
                    $this->matches[$entityId] = 1;
                    $match = 1;
                }
            }
        }

        if (!empty($removeCategories)) {
            $arrRemoveCategories = explode(",", $removeCategories);
            foreach ($arrRemoveCategories as $c) {
                $exists = $this->fetchCategoryId($entityId, $c);
                if ($exists) {
                    $this->removeCategoryFromProduct($entityId, $c);
                    $txt = "Category ID $c un-assigned from  : " . $product->getSku();
                    $this->logger->writeLogToFile($this->logViewFileName, $txt);
                    $this->matches[$entityId] = 1;
                    $match = 1;
                }
            }
        }

        if (!empty($rules)) {
            $unserialiseRules = unserialize($rules);
            $attributes = [];
            $attributes_codes = [];
            foreach ($unserialiseRules as $r) {
                if (isset($r['value'])) {
                    $values = $r['value'];
                    if (is_array($r['value'])) {
                        $values = implode(',', $r['value']);
                    }
                    if (empty($values)) {
                        $values = null;
                    }
                    $attributes[$r['attribute_code']] = $values;
                    $attributes_codes [] = $r['attribute_code'];
                }
            }
            if (count($attributes) > 0) {
                $stores = explode(",", $rule->getApplyToStores());
                foreach ($stores as $storeId) {
                    $this->updateProductAttributes($entityId, $attributes, $storeId);
                    $txt = "Attribute(s) " .implode(",", $attributes_codes) . ' updated for ' . $product->getSku().' store id =='.$storeId;
                    $this->logger->writeLogToFile($this->logViewFileName, $txt);
                }
                $this->matches[$entityId] = 1;
                $match = 1;
            }
        }

        if ($match == 1) {
            $currentCount = $rule->getLastrunUpdates();
            $ruleUpdate = $currentCount + $match;
            $rule->setLastrunUpdates($ruleUpdate);
            //save log file
            $rule->setDownloadLogFile($this->logViewFileName);
            $rule->save();
        }
    }

    /**
     * Check Category associated to product or not
     * @param int $entityId
     * @param int $categoryId
     * @return boolean
     */
    public function fetchCategoryId($entityId, $categoryId)
    {           
        $isCategory = (bool)$this->itemModel->fetchCategoryDetails($entityId, $categoryId);
        return $isCategory;
    }

    /**
     * Assign category to product
     * @param int $productId
     * @param int $categoryId
     */
    public function assignCategoryToProduct($productId, $categoryId)
    {
        $status = $this->itemModel->addCategoryToProduct($productId, $categoryId);
    }

    /**
     * un-associate(remove) category from product
     * @param int $productId
     * @param int $categoryId
     */
    public function removeCategoryFromProduct($productId, $categoryId)
    {
        $status = $this->itemModel->unassignCategoryFromProduct($productId, $categoryId);        
    }

    /**
     * update product attributes for the matching products
     * @param int $entityId
     * @param array|int $attributes
     * @param int $storeId
     */
    public function updateProductAttributes($entityId, $attributes, $storeId)
    {
        $this->action->updateAttributes([$entityId], $attributes, $storeId);
    }

    /**
     * Validate the expiry of rule
     * @param \Apoyar\ProductRules\Model\Rule $rule
     * @return boolean
     */
    public function validityOfRule($rule)
    {
        $Date = date('Y-m-d');
        $ruleDateBegin = date('Y-m-d', strtotime($rule->getFromDate()));
        $ruleDateEnd = date('Y-m-d', strtotime($rule->getToDate()));
        if ((($Date >= $ruleDateBegin) && ($Date <= $ruleDateEnd)) || (is_null($rule->getFromDate()) && is_null($rule->getToDate()))
        ) {
            return true;
        } else {
            return false;
        }
    }   
}
