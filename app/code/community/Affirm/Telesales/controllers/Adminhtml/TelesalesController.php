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

class Affirm_Telesales_Adminhtml_TelesalesController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Checkout Page On Affirm Payment Action
     *
     */
    public function checkoutAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('sales/orders');
        $this->_addContent($this->getLayout()->createBlock('affirm_telesales/adminhtml_checkout'));
        $this->renderLayout();
        return;
    }

    public function createCheckoutAction()
    {
        $result = new Varien_Object();
        try {
            $orderId = $this->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
            $payment = $order->getPayment()->getMethodInstance();
            if($payment->getCheckoutToken()){
                $token = $payment->getCheckoutToken();
                Mage::log(' Token already exist, resend checkout from existing token: '.$token);
                $tokenObj['checkout_id'] = $token;
                $response = $this->resendCheckoutAction($tokenObj);
            } else {
                $checkoutObj = Mage::getModel('affirm_telesales/telesales')->getCheckoutObject($orderId);
                $response = $this->sendCheckoutAction($checkoutObj);
            }
            if(isset($response['checkout_id'])) {
                $result = array(
                    'success' => true,
                    'error' => false,
                    'checkout_id' => $response['checkout_id'],
                    'redirect_url' => $response['redirect_url']
                );
                if(isset($token)){
                    $this->_getSession()->addSuccess($this->__('The Affirm checkout link has been sent again to the customer.'));
                } else {
                    $this->_getSession()->addSuccess($this->__('The Affirm checkout link has been sent to the customer.'));
                    $payment->setAffirmCheckoutToken($response['checkout_id']);
                    $order->addStatusHistoryComment(Mage::helper('affirm_telesales')->__('The Affirm checkout link has been sent to the customer. Checkout token:' . $response['checkout_id']))->save();
                    $order->save();
                }
            } else {
                Mage::log($response);
                $result = array(
                    'success' => false,
                    'error' => true,
                    'message' => $this->__('Unable to send checkout link. It’s either expired or already processed and therefore can not be resent to the customer. Please cancel this order and create new one to send new checkout link.')
                );
                $this->_getSession()->addError($this->__('Unable to send checkout link. It’s either expired or already processed and therefore can not be resent to the customer. Please cancel this order and create new one to send new checkout link.'));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $result->setSuccess(false);
        };
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function sendCheckoutAction($checkoutObj){
        $response = Mage::getModel('affirm_telesales/telesales')->sendCheckout($checkoutObj);
        return $response;
    }

    public function resendCheckoutAction($checkoutTokenObject){
        $response = Mage::getModel('affirm_telesales/telesales')->resendCheckout($checkoutTokenObject);
        return $response;
    }
}