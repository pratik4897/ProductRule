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
use \Magento\Store\Model\System\Store;

class Main extends Generic implements TabInterface
{

    protected $_systemStore;
    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Rule Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Rule Information');
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

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id']);
        }

        $fieldset->addField(
            'name',
            'text',
            ['name' => 'name', 'label' => __('Rule Name'), 'title' => __('Rule Name'), 'required' => true]
        );



        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => ['1' => __('Active'), '0' => __('Inactive')],
                'after_element_html' => "<p class='nm'><small>Set the rule status to be enabled or disabled</small></p>"
            ]
        );

        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }

        $fieldset->addField(
            'cronjob_slot',
            'select',
            [
                'label' => __('Cronjob Slot'),
                'title' => __('Cronjob Slot'),
                'name' => 'cronjob_slot',
                'required' => true,
                'options' => ['1' => __('Slot 1'), '2' => __('Slot 2')],
                'after_element_html' => "<p class='nm'><small>The rules cron will run on different slots to maintain resources. These slot timings can be modified in configuration settings</small></p>"
            ]
        );

        if (!$model->getId()) {
            $model->setData('cronjob_slot', '1');
        }

        $fieldset->addField(
            'is_dry_run',
            'select',
            [
                'label' => __('Is Dry Run'),
                'name' => 'is_dry_run',
                'title' => __('Is Dry Run'),
                'required' => false,
                'options' => [ '1' => __('Yes'),'0' => __('No')],
                'value' => 0,
                'after_element_html' => "<p class='nm'><small>For testing purposes; the products are not updated if is set to 'yes'; you can check the log to see which products were updated</small></p>"
            ]
        );
        $fieldset->addField(
            'apply_to_stores',
            'multiselect',
            [
                'name'    => 'apply_to_stores',
                'label'   => __('Apply to stores'),
                'values' => $this->_systemStore->getStoreValuesForForm(false, true),
                'required'=> true,
                'after_element_html'=>'<p class="nm"><small>The rule will only be applied to products from the selected stores</small></p>'
            ]
        );
        $productTypes = [
            [
                'value' => 'simple',
                'label' => 'Simple Product',
            ],
            [
                'value' => 'grouped',
                'label' => 'Grouped Product',
            ],
            [
                'value' => 'configurable',
                'label' => 'Configurable Product',
            ],
            [
                'value' => 'virtual',
                'label' => 'Virtual Product',
            ],
            [
                'value' => 'bundle',
                'label' => 'Bundle Product',
            ],
            [
                'value' => 'downloadable',
                'label' => 'Downloadable Product',
            ],
            [
                'value' => 'giftcard',
                'label' => 'Gift Card',
            ]]
        ;

        $fieldset->addField(
            'product_types',
            'multiselect',
            [
                'name'    => 'product_types',
                'label'   => __('Apply only to selected product types '),
                'values' => $productTypes,
                'required'=> true,
                'after_element_html'=> '<p class="nm"><small>The rule will only be applied to selected product types and that are not associated with a configurable product</small></p>'
            ]
        );

        $fieldset->addField('sort_order', 'text', ['name' => 'sort_order', 'label' => __('Priority')]);

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField(
            'from_date',
            'date',
            [
                'name' => 'from_date',
                'label' => __('From'),
                'title' => __('From'),
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => $dateFormat
            ]
        );
        $fieldset->addField(
            'to_date',
            'date',
            [
                'name' => 'to_date',
                'label' => __('To'),
                'title' => __('To'),
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => $dateFormat
            ]
        );

        $form->setValues($model->getData());

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);

        $this->_eventManager->dispatch('adminhtml_product_rule_edit_tab_main_prepare_form', ['form' => $form]);

        return parent::_prepareForm();
    }
}
