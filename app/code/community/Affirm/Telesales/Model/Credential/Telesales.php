<?php

/**
 * Class Affirm_Telesales_Model_Credential_Telesales
 */
class Affirm_Telesales_Model_Credential_Telesales extends Affirm_Affirm_Model_Credential
{
    /**
     * Payment affirm_telesales account mode
     */
    const PAYMENT_AFFIRM_ACCOUNT_MODE = 'payment/affirm_telesales/account_mode';

    /**
     * Info block type
     *
     * @var array
     */
    protected $_credentialModelsCache;

    /**
     * Get credential model due to current account type
     *
     * @param Mage_Core_Model_Store $store
     * @return mixed
     * @throws Affirm_Affirm_Exception
     */
    protected function _getCredentialModel($store = null)
    {
        $storeCacheId = $this->_getStoreIdForCache($store);
        if (!isset($this->_credentialModelsCache[$storeCacheId])) {
            $mode = Mage::getStoreConfig(self::PAYMENT_AFFIRM_ACCOUNT_MODE, $store);
            $modelClass = 'affirm_telesales/credential_' . $mode;
            $model = Mage::getModel($modelClass);
            if (!$model) {
                throw new Affirm_Affirm_Exception('Could not found model ' . $modelClass);
            }
            $this->_credentialModelsCache[$storeCacheId] = $model;
        }
        return $this->_credentialModelsCache[$storeCacheId];
    }
}