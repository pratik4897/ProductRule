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

namespace Apoyar\ProductRules\Model\ResourceModel\Rule;
use Magento\Framework\Data\Collection\EntityFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Collection extends \Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $date;

    /**
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param TimezoneInterface $date
     * @param AdapterInterface $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        TimezoneInterface $date,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->date = $date;
    }

    /**
     * Set resource model and determine field mapping
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Apoyar\ProductRules\Model\Rule', 'Apoyar\ProductRules\Model\ResourceModel\Rule');
    }

    /**
     * Filter collection by specified date.
     * Filter collection to only active rules.
     *
     * @param string|null $now
     * @use $this->addStoreGroupDateFilter()
     * @return $this
     */
    public function setValidationFilter($now = null)
    {
        if (!$this->getFlag('validation_filter')) {
            $this->addDateFilter($now);
            $this->addIsActiveFilter();
            $this->setOrder('sort_order', self::SORT_ORDER_DESC);
            $this->setFlag('validation_filter', true);
        }

        return $this;
    }

    /**
     * From date or to date filter
     *
     * @param $now
     * @return $this
     */
    public function addDateFilter($now)
    {
        $this->getSelect()->where(
            'from_date is null or from_date <= ?',
            $now
        )->where(
            'to_date is null or to_date >= ?',
            $now
        );

        return $this;
    }
}
