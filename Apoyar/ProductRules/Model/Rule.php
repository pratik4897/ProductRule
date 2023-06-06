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

use Magento\Rule\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\CatalogRule\Model\Rule\Condition\CombineFactory;
use Apoyar\ProductRules\Helper\Data;
use Apoyar\ProductRules\Helper\Logger;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

class Rule extends AbstractModel
{
    protected $_productIds;
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'apoyar_productrules';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getRule() in this case
     *
     * @var string
     */
    protected $_eventObject = 'rule';

    /** @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory */
    protected $condCombineFactory;
    protected $helperData;
    protected $logger;
    
    protected $collectionProduct;
    protected $productModel;
    protected $resourceIterator;
    protected $storeManager;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param CombineFactory $condCombineFactory
     * @param Data $helperData,
     * @param Collection $collectionProduct,
     * @param ProductFactory $productModel,
     * @param Iterator $resourceIterator,
     * @param StoreManagerInterface $storeManager,
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        CombineFactory $condCombineFactory,
        Data $helperData,
        Logger $logger,
        Collection $collectionProduct,
        ProductFactory $productModel,
        Iterator $resourceIterator,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->condCombineFactory = $condCombineFactory;
        $this->helperData = $helperData;
        $this->logger = $logger;
        $this->collectionProduct = $collectionProduct;
        $this->productModel = $productModel;
        $this->resourceIterator = $resourceIterator;
        $this->storeManager = $storeManager;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Apoyar\ProductRules\Model\ResourceModel\Rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Get rule condition combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * Getter for rule actions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    /**
     * Get array of product ids which are matched by rule
     * @param  mixed $testrun
     * @param  mixed $logfile
     * @return array
     */
    public function getListProductIdsInRule($testrun, $logfile)
    {
        $productCollection = $this->collectionProduct;

        $productFactory =  $this->productModel;
        $this->_productIds = [];
        $this->setCollectedAttributes([]);
        $ruleConditions =  $this->getConditions()->getConditions();
        $isAttribute = false;
        foreach ($ruleConditions as $condition) {
            $isAttribute = $this->checkAttribute($condition, null);
            if ($isAttribute) {
                break;
            }
        }
        if ($isAttribute) {
            $this->getConditions()->collectValidatedAttributes($productCollection);
            $this->resourceIterator->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product' => $productFactory->create(),
                    'testrun' => $testrun,
                    'vendorData' =>  $this->helperData,
                    'logfile' => $logfile
                ]
            );
        } else {
            $this->logger->logInvalidRule($logfile, $this->getRuleId(), $this->getName());
        }
        return $this->_productIds;
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $testRun = $args['testrun'];
        $product = clone $args['product'];
        $product->setData($args['row']);
        $vendorData =  $args['vendorData'];
        $logfile =  $args['logfile'];
        $websites = $this->_getWebsitesMap();
        $arrType = explode(",", $this->getProductTypes());
        foreach ($websites as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            if ($this->getConditions()->validate($product) && in_array($product->getTypeId(), $arrType)) {

                $this->_productIds[] = $product->getSku();
                if ($testRun != 1 && $this->getIsDryRun() != 1) {
                    $vendorData->addProductInLog($product, $this, $testRun, $logfile);
                }
            }
        }
    }

    /**
     * Prepare website map
     *
     * @return array
     */
    protected function _getWebsitesMap()
    {
        $map = [];
        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            // Continue if website has no store to be able to create catalog rule for website without store
            if ($website->getDefaultStore() === null) {
                continue;
            }
            $map[$website->getId()] = $website->getDefaultStore()->getId();
        }
        return $map;
    }
    
    /**
     * check if Attribute combination is valid or not
     *
     * @param  mixed $condition
     * @return void
     */
    protected function checkAttribute($condition)
    {
        $condtionType = $condition->getType();
        $conditionAttribute = $condition->getAttribute();
        if ($condtionType == "Magento\CatalogRule\Model\Rule\Condition\Product" && is_null($conditionAttribute)) {
            return false;
        } else {
            if ($condtionType == "Magento\CatalogRule\Model\Rule\Condition\Combine") {
                $getNestedConditions = $condition->getConditions();
                if (count($condition->getConditions()) > 0) {
                    foreach ($getNestedConditions as $nc) {
                        $condtionType = $nc->getType();
                        $conditionAttribute = $nc->getAttribute();
                        if ($condtionType == "Magento\CatalogRule\Model\Rule\Condition\Product" && is_null($conditionAttribute)) {
                            return false;
                        } else {
                            if ($condtionType == "Magento\CatalogRule\Model\Rule\Condition\Combine") {
                                $getNc = $nc->getConditions();
                                if (count($getNc) > 0) {
                                    foreach ($getNc as $ncc) {
                                        $condtionType = $ncc->getType();
                                        $conditionAttribute = $ncc->getAttribute();
                                        if ($condtionType == "Magento\CatalogRule\Model\Rule\Condition\Product" && is_null($conditionAttribute)) {
                                            return false;
                                        } else {
                                            if ($condtionType == "Magento\CatalogRule\Model\Rule\Condition\Combine") {
                                                if (count($ncc->getConditions()) == 0) {
                                                    return false;
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    return false;
                                }
                            }
                        }
                    }
                } else {
                    return false;
                }
            }
        }
        return true;
    }
}
