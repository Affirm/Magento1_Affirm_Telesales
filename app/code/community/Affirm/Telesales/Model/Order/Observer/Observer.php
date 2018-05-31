<?php
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