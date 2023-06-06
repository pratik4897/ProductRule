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

namespace Apoyar\ProductRules\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\App\ResourceConnection;

class Items extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const PRODUCTTABLE = 'catalog_category_product';

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;
    /**
     * constructor
     * @param ResourceConnection $resourceConnection
     * @param Context $context
     * @param $connectionName
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Context $context,
        $connectionName = null
    ) {
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $connectionName);
    }

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('apoyar_productrules', 'id');
    }
        
    /**
     * check whether category exists or not
     *
     * @param  int $entityId
     * @param  int $categoryId
     * @return bool|mixed
     */
    public function fetchCategoryDetails($entityId, $categoryId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(SELF::PRODUCTTABLE);
        $select = $connection->select();
        $select->from(
            ['c' => $tableName],
            ['count(*)']
        )->where("c.category_id = ?", $categoryId
        )->where("c.product_id = ?", $entityId);
        return $connection->fetchOne($select);
    } 
        
    /**
     * Add Category To Product
     *
     * @param  int $productId
     * @param  int $categoryId
     * @return void|int
     */
    public function addCategoryToProduct($productId, $categoryId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(SELF::PRODUCTTABLE);
        $status = $connection->insert($tableName, ["category_id"=>$categoryId,"product_id"=>$productId]);
        return $status;
    }
    
    /**
     * Remove Category From Product
     *
     * @param  int $productId
     * @param  int $categoryId
     * @return void|int
     */
    public function unassignCategoryFromProduct($productId, $categoryId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(SELF::PRODUCTTABLE);
        $where = ['category_id = ?'=> $categoryId,'product_id=?'=>$productId];
        $status = $connection->delete($tableName,$where);
        return $status;
    }
    
    /**
     * Get All Enabled(Active) Rules available for the given slot
     *
     * @param int $slot
     * @return void|array
     */
    public function getAllEnabledRules($slot)
    {
        $connection = $this->resourceConnection->getConnection();

        $now = new \DateTime();
        $query = 'SELECT rule_id FROM apoyar_productrules WHERE is_active = 1 
            AND is_dry_run = 0 
            AND cronjob_slot = '.$slot.'
            AND (
                  (from_date <= "'.$now->format('Y-m-d').'" AND to_date >="'.$now->format('Y-m-d').'")
                    OR 
                  (from_date is null AND to_date is null)
                ) ORDER BY sort_order ASC';
        return $connection->fetchAll($query);
    } 
}
