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
 * Class Affirm_Telesales_PaymentController
 */
class Affirm_Telesales_PaymentController extends Mage_Checkout_Controller_Action
{
    /**
     * Confirm checkout
     */
    public function confirmAction()
    {
        $checkoutToken = $this->getRequest()->getParam('checkout_token');
        if (!$checkoutToken) {
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = $this->__('We weren’t able to process your order either because of a processing error or an issue with your loan application. We’re sorry for the inconvenience. Please contact us or try again later.');
            Mage::log('Missing Affirm checkout token');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            $failureUrl = Mage::getUrl('telesales/payment/failure');
            $this->_redirectUrl($failureUrl);
            return;
        } else {
            $response = Mage::getModel('affirm_telesales/telesales')->getCheckoutFromToken($checkoutToken);
            $order = Mage::getModel('sales/order');
            $incrementId = $response['merchant_external_reference'];

            if($incrementId) {
                try {
                    $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
                    if ($order->getId()) {
                        $storeId = $order->getStoreId();
                        $paymentStatus = $order->getStatus();
                        if($paymentStatus == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                            Mage::getModel('affirm_telesales/telesales')->processConfirmOrder($order, $checkoutToken);
                            Mage::getSingleton('checkout/session')->setLastQuoteId($order->getId())
                                ->setLastSuccessQuoteId($order->getId())
                                ->clearHelperData();
                            Mage::getSingleton('checkout/session')->setLastOrderId($order->getId())
                                 ->setLastRealOrderId($order->getIncrementId());
                            $order->sendNewOrderEmail();
                            $this->_redirect('checkout/onepage/success');
                            return;
                        } else {
                            Mage::log('Order is already processed');
                            Mage::getSingleton('core/session')->addError($this->__('Your order has already been processed. Please contact us for more information about your order.'));
                            $redirectUrl = Mage::getUrl('telesales/payment/failure');
                            $this->_redirectUrl($redirectUrl);
                            return;
                        }
                    }
                } catch (Affirm_Affirm_Exception $e) {
                    Mage::logException($e);
                    Mage::log('Error in order processing during confirmation');
                    Mage::getSingleton('core/session')->addError($this->__('We couldn’t find your order. Please contact us or try again later.'));
                    $this->getResponse()->setRedirect(Mage::getUrl('telesales/payment/failure'))->sendResponse();
                    return;
                } catch (Mage_Core_Exception $e) {
                    Mage::logException($e);
                    Mage::log('Error in order processing during confirmation');
                    Mage::getSingleton('core/session')->addError($this->__('We weren’t able to process your order either because of a processing error or an issue with your loan application. We’re sorry for the inconvenience. Please contact us or try again later.'));
                    $this->getResponse()->setRedirect(Mage::getUrl('telesales/payment/failure'))->sendResponse();
                    return;
                }
            }  else {
                Mage::log('Missing merchant external reference.');
                Mage::throwException(
                    Mage::helper('affirm_telesales')->__('We weren’t able to process your order either because of a processing error or an issue with your loan application. We’re sorry for the inconvenience. Please contact us or try again later.')
                );
                $redirectUrl = Mage::getUrl('telesales/payment/failure');
                $this->_redirectUrl($redirectUrl);
                return;
            }
        }
    }

    public function cancelAction()
    {
        $result['success']  = false;
        $result['error']    = true;
        $result['error_messages'] = $this->__('We weren’t able to process your order either because of a processing error or an issue with your loan application. We’re sorry for the inconvenience. Please contact us or try again later.');

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        $redirectUrl = Mage::getUrl('telesales/payment/failure');
        $this->_redirectUrl($redirectUrl);
        return;
    }

    public function declineAction()
    {
        $result['success']  = false;
        $result['error']    = true;
        $result['error_messages'] = $this->__('We weren’t able to process your order either because of a processing error or an issue with your loan application. We’re sorry for the inconvenience. Please contact us or try again later.');

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        $redirectUrl = Mage::getUrl('telesales/payment/failure');
        $this->_redirectUrl($redirectUrl);
        return;
    }

    public function failureAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}
