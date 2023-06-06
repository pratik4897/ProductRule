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

class TestRun extends \Apoyar\ProductRules\Controller\Adminhtml\Product\Rule
{

    protected $helperData;

    protected $resultRawFactory;

    protected $fileFactory;

    /**
     * Test rule action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Apoyar\ProductRules\Model\Rule $model */
        $model = $this->ruleFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getRuleId()) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                $this->_redirect('apoyar_productrules/*');
                return;
            }
        }

        $logFile = $this->helperData->processRule($model, 1);
        if (file_exists($logFile)) {
            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename='.basename($logFile));
            header('Pragma: no-cache');
            readfile($logFile);
            exit;
        }
    }
}
