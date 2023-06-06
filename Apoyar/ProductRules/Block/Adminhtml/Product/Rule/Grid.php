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

namespace Apoyar\ProductRules\Block\Adminhtml\Product\Rule;

class Grid extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Prepare title and buttons for grid
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Apoyar_ProductRules';
        $this->_controller = 'adminhtml_product_rule';
        $this->_headerText = __('Product Rules');
        $this->_addButtonLabel = __('Add New Rule');
        parent::_construct();
    }

    /**
     * Prepares mass action for grid
     * @return Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');

        $this->getMassactionBlock()->addItem(
            'massDelete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('apoyar_productrules/product_rule/massDelete')
            ]
        );

        return $this;
    }
}
