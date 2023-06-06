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

namespace Apoyar\ProductRules\Controller\Search;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Eav\Model\Config;
use Apoyar\ProductRules\Helper\Data as rulehelper;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;
    protected $jsonHelper;
    protected $eavConfig;
    protected $helperData;
    /**
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $jsonHelper
     * @param Config $eavConfig
     * @param rulehelper $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $jsonHelper,
        Config $eavConfig,
        rulehelper $helperData
    ) {
        $this->resultFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->eavConfig = $eavConfig;
        $this->helperData = $helperData;
        parent::__construct($context);
    }
    /**
     * AttributeValue action
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->helperData->isEnabled()) {
            return;
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        if (!$this->getRequest()->getParam('attribute_code')) {
            return false;
        }
        $attributeCode = $this->getRequest()->getParam('attribute_code');
        $rowIndex = $this->getRequest()->getParam('row_index');
        $attributeData = [];
        $valueField = "";
        if (!empty($attributeCode)) {
            $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
            $valueField = "";
            if ($attribute->getFrontendInput() == "boolean" || $attribute->getFrontendInput() == "select" || $attribute->getFrontendInput() == "multiselect") {
                $multiple = '';
                $name = "rules[$rowIndex][value]";
                if ($attribute->getFrontendInput() == "multiselect") {
                    $multiple = "multiple";
                    $name = "rules[$rowIndex][value][]";
                }
                $options = $attribute->getSource()->getAllOptions();
                $valueField = '<select name="'.$name.'" '.$multiple.'  title="Attribute"  class="input-text admin__control-text" style="">';
                foreach ($options as $opt) {
                    $valueField .= '<option value="'.$opt['value'].'">'.$opt['label'].'</option>';
                }

                $valueField .= '</select>';
            } elseif ($attribute->getFrontendInput() == "weight"
                || $attribute->getFrontendInput() == "date"
                || $attribute->getFrontendInput() == "decimal"
                || $attribute->getFrontendInput() == "text"
                || $attribute->getFrontendInput() == "price") {

                $valueField = '<input name="rules['.$rowIndex.'][value]" value="" title="Value" type="text" class="input-text admin__control-text attribute-value" style="">';

            } elseif ($attribute->getFrontendInput() == "textarea") {
                $valueField = '<textarea name="rules['.$rowIndex.'][value]"  title="Value" class="textarea" rows="3" cols="3" aria-hidden="true" style=""></textarea>';
            }
        }
        $response->setContents(
            $this->jsonHelper->jsonEncode(
                ['field' =>$valueField]
            )
        );
        return $response;
    }
}
