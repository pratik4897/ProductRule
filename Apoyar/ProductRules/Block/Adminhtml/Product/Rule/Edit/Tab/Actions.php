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

namespace Apoyar\ProductRules\Block\Adminhtml\Product\Rule\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class Actions extends Generic implements TabInterface
{

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Actions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Actions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Generic
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_rule');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset_category = $form->addFieldset('assign_categories', ['legend' => __('Associate Categories'),'collapsible'  => true]);

        $fieldset_category->addField(
            'categories',
            'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Category',
            [
                'name' => 'categories',
                'label' => __('Categories'),
                'required' => false,
            ]
        );
        $fieldset_category = $form->addFieldset('remove_categories', ['legend' => __('Remove Categories'),'collapsible'  => true]);

        $fieldset_category->addField(
            'unassociate_categories',
            'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Category',
            [
                'name' => 'unassociate_categories',
                'label' => __('Remove Categories'),
                'required' => false,
            ]
        );
        $fieldset = $form->addFieldset('attribute_matching', ['legend' => __('Conditional Attribute'),'collapsible'  => true]);


        $form->setValues($model->getData());

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
