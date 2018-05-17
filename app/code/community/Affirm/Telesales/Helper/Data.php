<?php
/**
 * Class Affirm_Telesales_Helper_Data
 */
class Affirm_Telesales_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $order;

    /**
     * Merchant Name
     */
    const MERCHANT_NAME = 'payment/affirm_telesales/company_name';

    /**
     * Payment Action
     */
    const PAYMENT_ACTION = 'payment/affirm_telesales/payment_action';


    public function isEnabled(){
        return Mage::helper('core')->isModuleEnabled('Affirm_Telesales');
    }
    /**
     * Returns extension version
     *
     * @return string
     */
    public function getExtensionVersion()
    {
        return (string)Mage::getConfig()->getNode()->modules->Affirm_Telesales->version;
    }

    /**
     * Get api url
     *
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getApiUrl()
    {
        return Mage::getSingleton('affirm_telesales/credential_telesales')->getApiUrl();
    }

    /**
     * Get api key
     *
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getApiKey($store = null)
    {
        if($store == null) {
            $store = Mage::app()->getStore()->getStoreId();
        }
        return Mage::getSingleton('affirm_telesales/credential_telesales')->getApiKey($store);
    }

    /**
     * Get secret key
     *
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getSecretKey($store = null)
    {
        if($store == null) {
            $store = Mage::app()->getStore()->getStoreId();
        }
        return Mage::getSingleton('affirm_telesales/credential_telesales')->getSecretKey($store);
    }


    public function getOrder() {
        if (is_null($this->order)) {
            if (Mage::registry('current_order')) {
                $order = Mage::registry('current_order');
            }
            elseif (Mage::registry('order')) {
                $order = Mage::registry('order');
            }
            else {
                $order = new Varien_Object();
            }
            $this->order = $order;
        }
        return $this->order;
    }

    public function getCurrentOrderId(){
        return $this->getOrder()->getId();
    }

    public function getPaymentMethod(){
        return $this->getOrder()->getPayment()->getMethod();
    }

    protected function isAffirmPaymentMethod(){
        $telesalesEnabled = $this->isEnabled();
        if ($this->getPaymentMethod() == Affirm_Affirm_Model_Payment::METHOD_CODE && $telesalesEnabled){
            return true;
        }
        return false;
    }

    protected function isPaymentPending(){
        if($this->getOrder()->getState() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT){
            return true;
        }
        return false;
    }

    public function showTelesalesCheckout(){
        if ($this->isAffirmPaymentMethod() && $this->isPaymentPending()) {
            return true;
        }
        return false;
    }

    public function getMerchantName($store = null){
        if($store == null) {
            $store = Mage::app()->getStore()->getStoreId();
        }
        $merchantName = (Mage::getStoreConfig(self::MERCHANT_NAME, $store)? Mage::getStoreConfig(self::MERCHANT_NAME, $store) : Mage::getModel('core/store')->load($store)->getWebsiteName());
        return $merchantName;
    }

    public function getPaymentAction($store = null){
        if($store == null) {
            $store = Mage::app()->getStore()->getStoreId();
        }
        $paymentAction = Mage::getStoreConfig(self::PAYMENT_ACTION, $store);
        return $paymentAction;
    }

    public function checkTokenExist(){
        if ($this->getOrder()->getPayment()->getMethodInstance()->getCheckoutToken()) {
            return true;
        }
        return false;
    }
}
