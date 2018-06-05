<?php
/**
 *
 *  * BSD 3-Clause License
 *  *
 *  * Copyright (c) 2018, Affirm
 *  * All rights reserved.
 *  *
 *  * Redistribution and use in source and binary forms, with or without
 *  * modification, are permitted provided that the following conditions are met:
 *  *
 *  *  Redistributions of source code must retain the above copyright notice, this
 *  *   list of conditions and the following disclaimer.
 *  *
 *  *  Redistributions in binary form must reproduce the above copyright notice,
 *  *   this list of conditions and the following disclaimer in the documentation
 *  *   and/or other materials provided with the distribution.
 *  *
 *  *  Neither the name of the copyright holder nor the names of its
 *  *   contributors may be used to endorse or promote products derived from
 *  *   this software without specific prior written permission.
 *  *
 *  * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 *  * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 *  * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 *  * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 *  * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 *  * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 *  * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 *  * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 *  * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 *  * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

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
