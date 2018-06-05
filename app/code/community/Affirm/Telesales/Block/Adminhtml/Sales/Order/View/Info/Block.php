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

class Affirm_Telesales_Block_Adminhtml_Sales_Order_View_Info_Block extends Mage_Core_Block_Template {

    protected function _prepareLayout()
    {
        $this->setChild('send_checkout_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => $this->helper('affirm_telesales')->__('Send Affirm Checkout link'),
                    'onclick' => 'AFFIRM_AFFIRM.telesales.createCheckout(\'' .  $this->getCreateCheckoutAjaxUrl() . '\');',
                    'class'  => 'save',
                    'style'     => $this->helper('affirm_telesales')->checkTokenExist() ? 'display: none;' : ''
                ))
        );
        $this->setChild('resend_checkout_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => $this->helper('affirm_telesales')->__('Re-Send Affirm Checkout link'),
                    'onclick' => 'AFFIRM_AFFIRM.telesales.resendCheckout(\'' .  $this->getCreateCheckoutAjaxUrl() . '\');',
                    'class'  => 'save',
                    'style'     => $this->helper('affirm_telesales')->checkTokenExist() ? '' : 'display: none;'
                ))
        );
        return parent::_prepareLayout();
    }

    /**
     * Get page header text
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->helper('affirm_telesales')->__('The customer will receive an email and SMS with the Affirm checkout link to begin their loan application. After confirming the loan, they’ll be redirected to your site. If they abandon the loan application or if their loan is declined, they’ll be redirected to an error message on your site.');
    }

    /**
     * Get html code of send checkout button
     *
     * @return string
     */
    public function getSendCheckoutButtonHtml()
    {
        return $this->getChildHtml('send_checkout_button');

    }

    /**
     * Get html code of send checkout button
     *
     * @return string
     */
    public function getReSendCheckoutButtonHtml()
    {
        return $this->getChildHtml('resend_checkout_button');

    }

    /**
     * Get ajax create checkout url
     *
     * @return string
     */
    public function getCreateCheckoutAjaxUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/telesales/createCheckout',
            array('order_id'=>$this->helper('affirm_telesales')->getOrder()->getId(),'_secure' => Mage::app()->getStore()->isCurrentlySecure()));
    }

}