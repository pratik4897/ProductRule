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

namespace Apoyar\ProductRules\Controller\Adminhtml\Product\Rule;

class Index extends \Apoyar\ProductRules\Controller\Adminhtml\Product\Rule
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('Product Rules'), __('Product Rules'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Product Rules'));
        $this->_view->renderLayout('root');
    }
}
