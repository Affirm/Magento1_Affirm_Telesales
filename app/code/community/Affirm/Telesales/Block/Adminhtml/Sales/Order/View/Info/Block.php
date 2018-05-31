<?php
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
        return $this->helper('affirm_telesales')->__('The customer will receive an email and SMS with the Affirm checkout link to begin their loan application. After confirming the loan, they’ll be redirected to your site. If they abandon the loan application or if their loan is declined, they’ll be redirected to an error message on your site. Selecting Affirm as a payment option requires enabling Telesales in Magento.');
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