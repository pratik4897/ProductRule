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

class Save extends \Apoyar\ProductRules\Controller\Adminhtml\Product\Rule
{
    /**
     * Rule save action
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->getRequest()->getPostValue()) {
            $this->_redirect('apoyar_productrules/*/');
        }
        try {
            /** @var $model \Apoyar\ProductRules\Model\Rule */
            $model = $this->ruleFactory->create();
            $this->_eventManager->dispatch(
                'adminhtml_controller_apoyar_productrules_prepare_save',
                ['request' => $this->getRequest()]
            );
            $data = $this->getRequest()->getPostValue();

            if (isset($data['categories'])) {
                $data['categories'] = implode(",", $data['categories']);
            } else {
                $data['categories'] = '';
            }

            if (isset($data['unassociate_categories'])) {
                $data['unassociate_categories'] = implode(",", $data['unassociate_categories']);
            } else {
                $data['unassociate_categories'] = '';
            }

            if (isset($data['apply_to_stores'])) {
                $data['apply_to_stores'] = implode(",", $data['apply_to_stores']);
            }
            if (isset($data['product_types'])) {
                $data['product_types'] = implode(",", $data['product_types']);
            }
            foreach ($data['rules'] as $key => $r) {
                if ($r['attribute_code'] =="") {
                    unset($data['rules'][$key]);
                }
            }
            $data['rules'] = serialize($data['rules']);
            $id = $this->getRequest()->getParam('rule_id');
            if ($id) {
                $model->load($id);
            }

            $validateResult = $model->validateData(new \Magento\Framework\DataObject($data));
            if ($validateResult !== true) {
                foreach ($validateResult as $errorMessage) {
                    $this->messageManager->addErrorMessage($errorMessage);
                }
                $this->_session->setPageData($data);
                $this->_redirect('apoyar_productrules/*/edit', ['id' => $model->getId()]);
                return;
            }

            $data = $this->prepareData($data);
            $model->loadPost($data);

            $this->_session->setPageData($model->getData());

            $model->save();
            $this->messageManager->addSuccessMessage(__('You saved the rule.'));
            $this->_session->setPageData(false);
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('apoyar_productrules/*/edit', ['id' => $model->getId()]);
                return;
            }
            $this->_redirect('apoyar_productrules/*/');
            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $id = (int)$this->getRequest()->getParam('rule_id');
            if (!empty($id)) {
                $this->_redirect('apoyar_productrules/*/edit', ['id' => $id]);
            } else {
                $this->_redirect('apoyar_productrules/*/new');
            }
            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the rule data. Please review the error log.')
            );
            $this->logger->critical($e);
            $data = !empty($data) ? $data : [];
            $this->_session->setPageData($data);
            $this->_redirect('apoyar_productrules/*/edit', ['id' => $this->getRequest()->getParam('rule_id')]);
            return;
        }
    }

    /**
     * Prepares specific data
     *
     * @param array $data
     * @return array
     */
    protected function prepareData($data)
    {

        if (isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];
        }

        unset($data['rule']);

        return $data;
    }
}
