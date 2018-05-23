<?php
/**
 * Class Affirm_Telesales_Model_Credential_Sandbox
 */
class Affirm_Telesales_Model_Credential_Sandbox extends Affirm_Affirm_Model_Credential_Abstract
{
    /**
     * Payment affirm_telesales api url
     */
    const PAYMENT_AFFIRM_API_URL = 'https://sandbox.affirm.com';

    /**
     * Payment affirm_telesales api key
     */
    const PAYMENT_AFFIRM_API_KEY = 'payment/affirm_telesales/api_key_sandbox';

    /**
     * Payment affirm_telesales secret key
     */
    const PAYMENT_AFFIRM_SECRET_KEY = 'payment/affirm_telesales/secret_key_sandbox';

    /**
     * Get api url
     *
     * @return string
     */
    public function getApiUrl()
    {
        return self::PAYMENT_AFFIRM_API_URL;
    }

    /**
     * Get api key
     *
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getApiKey($store = null)
    {
        return Mage::getStoreConfig(self::PAYMENT_AFFIRM_API_KEY, $store);
    }

    /**
     * Get secret key
     *
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getSecretKey($store = null)
    {
        return Mage::getStoreConfig(self::PAYMENT_AFFIRM_SECRET_KEY, $store);
    }
}
