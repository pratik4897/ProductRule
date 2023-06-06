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

namespace Apoyar\ProductRules\Block\Adminhtml\Data\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Block\Context;

class Storeview extends AbstractRenderer
{
    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * Enabled constructor.
     * @param Context $context
     * @param StoreManagerInterface $storemanager
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storemanager,
        array $data = []
    ) {
        $this->_storeManager = $storemanager;
        parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
    }

    /**
     * Used to render store field values
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $applyStores = explode(',', $row->getApplyToStores());
        $html = "";
        if (count($applyStores)>0) {
            $html ="<ul>";
            foreach ($applyStores as $value) {
                $store = $this->_storeManager->getStore($value);
                if ($store->getName()) {
                    $name = $store->getName();
                    if ($value == 0) {
                        $name = "All Store Views";
                    }
                    $html .= "<li>".$name."</li>";
                }
            }
            $html .= "</ul>";
        }
        return $html ;
    }
}
