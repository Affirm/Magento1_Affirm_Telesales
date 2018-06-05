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

class Affirm_Telesales_Model_Order_Observer_Observer {

    // This function is called on core_block_abstract_to_html_after event
    // We will append our block to the html
    public function getSalesOrderViewInfo(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        if (($block->getNameInLayout() == 'order_info') && ($child = $block->getChild('affirm-telesales-payment-block'))) {
            if (Mage::helper('affirm_telesales')->showTelesalesCheckout()) {
                $transport = $observer->getTransport();
                if ($transport) {
                    $html = $transport->getHtml();
                    $html .= $child->toHtml();
                    $transport->setHtml($html);
                }
            }
        }
    }

    public function execute()
    {
        $request = Mage::app()->getRequest();
        $paymentData = $request->getPost('payment');
        $telesalesEnabled = Mage::helper('core')->isModuleEnabled('Affirm_Telesales');
        if ($paymentData && isset($paymentData['method']) &&
            $paymentData['method'] == Affirm_Affirm_Model_Payment::METHOD_CODE &&
            $telesalesEnabled
        ) {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('affirm')->__('Click the Send Affirm Checkout Link button below to send the customer the checkout link to begin their loan application.'));
        }
    }
}