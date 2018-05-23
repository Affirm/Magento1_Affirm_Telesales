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
}