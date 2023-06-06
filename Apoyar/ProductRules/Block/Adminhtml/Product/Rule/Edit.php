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
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Apoyar\ProductRules\Model\Rule;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\SearchCriteriaInterface;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    protected $ruleModel;
    protected $attributeResource;
    protected $configModel;
    protected $searchCriteria;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Rule $ruleModel
     * @param AttributeRepository $attributeResource
     * @param Config $configModel
     * @param SearchCriteriaInterface $searchCriteria
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Rule $ruleModel,
        AttributeRepository $attributeResource,
        Config $configModel,
        SearchCriteriaInterface $searchCriteria,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->ruleModel = $ruleModel;
        $this->attributeResource = $attributeResource;
        $this->configModel = $configModel;
        $this->searchCriteria = $searchCriteria;
        parent::__construct($context, $data);
    }

    /**
     * Initialize form
     * Add standard buttons
     * Add "Save and Continue" button
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_product_rule';
        $this->_blockGroup = 'Apoyar_ProductRules';

        parent::_construct();

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class' => 'save',
                'label' => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            10
        );
        $this->buttonList->add(
            'test_run',
            [
                'class' => 'save primary',
                'label' => __('Test Run'),
                'onclick' => 'setLocation(\'' . $this->getUrl(
                        'apoyar_productrules/*/testRun',
                        ['id' => $this->getRequest()->getParam('id')]
                    ) . '\')',
            ],
            10
        );
        $this->buttonList->add(
            'download_log',
            [
                'label' => __('Download Log'),
                'onclick' => 'setLocation(\'' . $this->getUrl(
                        'apoyar_productrules/*/downloadLog',
                        ['id' => $this->getRequest()->getParam('id')]
                    ) . '\')',
            ],
            10
        );
    }

    /**
     * Getter for form header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $rule = $this->coreRegistry->registry('current_rule');
        if ($rule->getRuleId()) {
            return __("Edit Rule '%1'", $this->escapeHtml($rule->getName()));
        } else {
            return __('New Rule');
        }
    }

    /**
     * Prepare form Html. call the phtm file with form.
     *
     * @return string
     */
    public function getFormHtml()
    {
        // get the current form as html content.
        $html = parent::getFormHtml();
        //Append the phtml file after the form content.
        $html .= $this->setTemplate('Apoyar_ProductRules::messages/form.phtml')->toHtml();
        return $html;
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {

        $this->_formScripts[] = "
            require([
                'jquery',
                'mage/mage',
                'knockout'
            ], function ($){
            
            });
        ";
        return parent::_prepareLayout();
    }

    /**
     * get the rule by id
     * @param mixed $id
     * @return mixed
     */
    public function getRuleById($id)
    {
        return $this->ruleModel->load($id);
    }

    /**
     * get Attribute Resource value
     *
     * @return \Magento\Eav\Api\Data\AttributeSearchResultsInterface
     */
    public function getAttributeResource()
    {
        return $this->attributeResource->getList('catalog_product', $this->searchCriteria);
    }

    /**
     * getAttributeByAttributeCode
     *
     * @param  mixed $attrbuteCode
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function getAttributeByAttributeCode($attrbuteCode)
    {
        return $this->configModel->getAttribute('catalog_product', $attrbuteCode);
    }
}
