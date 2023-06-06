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

namespace Apoyar\ProductRules\Model\Rule\Condition;

/**
 * Catalog Rule Product Condition data model
 */
class Product extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    /**
     * Validate product attribute value for condition
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $attrCode = $this->getAttribute();
        if ('category_ids' == $attrCode) {
            return parent::validate($model);
        }
        $op =  $this->getOperatorForValidate();
        $oldAttrValue = $model->getData($attrCode);
        if ($oldAttrValue === null) {
            if ($op == 'null' || $op == 'notnullno') {
                return true;
            }
            return false;
        }

        if ($op == 'notnullyes' && ($oldAttrValue == '1' || $oldAttrValue != "")) {
            return true;
        }
        if ($op == 'notnullno' && ($oldAttrValue == '0' || $oldAttrValue == "" || $oldAttrValue == null)) {
            return true;
        }
        if ($op == 'empty' && $oldAttrValue == '') {
            return true;
        }

        $this->_setAttributeValue($model);
        $result = $this->validateAttribute($model->getData($attrCode));
        $this->_restoreOldAttrValue($model, $oldAttrValue);

        return (bool)$result;
    }
}
