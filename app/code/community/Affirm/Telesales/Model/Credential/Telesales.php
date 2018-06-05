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
 * Class Affirm_Telesales_Model_Credential_Telesales
 */
class Affirm_Telesales_Model_Credential_Telesales extends Affirm_Affirm_Model_Credential
{
    /**
     * Payment affirm_telesales account mode
     */
    const PAYMENT_AFFIRM_ACCOUNT_MODE = 'payment/affirm_telesales/account_mode';

    /**
     * Info block type
     *
     * @var array
     */
    protected $_credentialModelsCache;

    /**
     * Get credential model due to current account type
     *
     * @param Mage_Core_Model_Store $store
     * @return mixed
     * @throws Affirm_Affirm_Exception
     */
    protected function _getCredentialModel($store = null)
    {
        $storeCacheId = $this->_getStoreIdForCache($store);
        if (!isset($this->_credentialModelsCache[$storeCacheId])) {
            $mode = Mage::getStoreConfig(self::PAYMENT_AFFIRM_ACCOUNT_MODE, $store);
            $modelClass = 'affirm_telesales/credential_' . $mode;
            $model = Mage::getModel($modelClass);
            if (!$model) {
                throw new Affirm_Affirm_Exception('Could not found model ' . $modelClass);
            }
            $this->_credentialModelsCache[$storeCacheId] = $model;
        }
        return $this->_credentialModelsCache[$storeCacheId];
    }
}