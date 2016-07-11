<?php

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category  PayIntelligent
 * @package   ratepay
 * @copyright (C) 2012 PayIntelligent GmbH  <http://www.payintelligent.de/>
 * @license   GPLv2
 */

/**
 * HeadInfo model
 */
class rpHeadInfo
{
    /**
     * Transaction id
     * 
     * @var string
     */
    private $_transactionId;
    
    /**
     * Transaction short id
     * 
     * @var string
     */
    private $_transactionShortId;

    /**
     * Subtype
     * 
     * @var string
     */
    private $_subtype;

    /**
     * Profile id
     * 
     * @var string
     */
    private $_profileId;

    /**
     * Security code
     * 
     * @var string
     */
    private $_securityCode;

    /**
     * Order id
     * 
     * @var string
     */
    private $_orderId;
    
    /**
     * system id (server ip)
     * 
     * @var string 
     */
    private $_systemId;
    
    /**
     * Shop version
     * 
     * @var string 
     */
    private $_shopVersion;
    
    /**
     * Module version
     * 
     * @var string
     */
    private $_moduleVersion;
    
    /**
     * Shop system
     * 
     * @var string
     */
    private $_shopSystem;

    /**
     * Device site/snipped id
     *
     * @var string
     */
    private $_deviceSite;

    /**
     * Device Token
     *
     * @var string
     */
    private $_deviceToken;

    /**
     * Set system id
     */
    public function __construct()
    {
        $this->_systemId = $_SERVER['SERVER_ADDR'];
    }

    /**
     * Retrieve system id
     * 
     * @return string
     */
    public function getSystemId()
    {
        return $this->_systemId;
    }

    /**
     * Set system id
     * 
     * @param string $systemId
     * @return rpHeadInfo
     */
    public function setSystemId($systemId)
    {
        $this->_systemId = $systemId;
        
        return $this;
    }

    /**
     * Retrieve shop version
     * 
     * @return string
     */
    public function getShopVersion()
    {
        return $this->_shopVersion;
    }

    /**
     * Set shop version
     * 
     * @param string $shopVersion
     * @return rpHeadInfo
     */
    public function setShopVersion($shopVersion)
    {
        $this->_shopVersion = $shopVersion;
        
        return $this;
    }

    /**
     * Retrieve module version
     * 
     * @return string
     */
    public function getModuleVersion()
    {
        return $this->_moduleVersion;
    }

    /**
     * Set module version 
     * 
     * @param string $moduleVersion
     * @return rpHeadInfo
     */
    public function setModuleVersion($moduleVersion)
    {
        $this->_moduleVersion = $moduleVersion;
        
        return $this;
    }

    /**
     * Retrieve shop system name
     * 
     * @return string
     */
    public function getShopSystem()
    {
        return $this->_shopSystem;
    }

    /**
     * Set shop system name
     * 
     * @param string $shopSystem
     * @return rpHeadInfo
     */
    public function setShopSystem($shopSystem)
    {
        $this->_shopSystem = $shopSystem;
        
        return $this;
    }

    /**
     * Get transaction id
     * @return string
     */
    public function getTransactionId()
    {
        return $this->_transactionId;
    }
    
    /**
     * Retrieve transaction short id
     * 
     * @return string
     */
    public function getTransactionShortId()
    {
        return $this->_transactionShortId;
    }

    /**
     * Set transaction short id
     * 
     * @param string $transactionShortId
     * @return rpHeadInfo
     */
    public function setTransactionShortId($transactionShortId)
    {
        $this->_transactionShortId = $transactionShortId;
        
        return $this;
    }

    
    /**
     * Set transaction id
     * 
     * @param string $transactionId
     * @return rpHeadInfo
     */
    public function setTransactionId($transactionId)
    {
        $this->_transactionId = $transactionId;

        return $this;
    }

    /**
     * Get subtype
     * 
     * @return string
     */
    public function getSubtype()
    {
        return $this->_subtype;
    }

    /**
     * Set subtype
     * 
     * @param string $subtype
     * @return rpHeadInfo
     */
    public function setSubtype($subtype)
    {
        $this->_subtype = $subtype;

        return $this;
    }

    /**
     * Get profile id
     * 
     * @return string
     */
    public function getProfileId()
    {
        return $this->_profileId;
    }

    /**
     * Set profile id
     * 
     * @param string $profileId
     * @return rpHeadInfo
     */
    public function setProfileId($profileId)
    {
        $this->_profileId = $profileId;

        return $this;
    }

    /**
     * Get security code
     * 
     * @return string
     */
    public function getSecurityCode()
    {
        return $this->_securityCode;
    }

    /**
     * Set security code
     * 
     * @param string $securityCode
     * @return rpHeadInfo
     */
    public function setSecurityCode($securityCode)
    {
        $this->_securityCode = $securityCode;

        return $this;
    }

    /**
     * Get order id
     * 
     * @return string
     */
    public function getOrderId()
    {
        return $this->_orderId;
    }

    /**
     * Set order id
     * 
     * @param string $orderId
     * @return rpHeadInfo
     */
    public function setOrderId($orderId)
    {
        $this->_orderId = $orderId;

        return $this;
    }

    /**
     * Get order id
     *
     * @return string
     */
    public function getDeviceSite()
    {
        return $this->_deviceSite;
    }

    /**
     * Set device site/snipped id
     *
     * @param string $deviceSite
     * @return rpHeadInfo
     */
    public function setDeviceSite($deviceSite)
    {
        $this->_deviceSite = $deviceSite;

        return $this;
    }

    /**
     * Get order id
     *
     * @return string
     */
    public function getDeviceToken()
    {
        return $this->_deviceToken;
    }

    /**
     * Set device token
     *
     * @param string $deviceToken
     * @return rpHeadInfo
     */
    public function setDeviceToken($deviceToken)
    {
        $this->_deviceToken = $deviceToken;

        return $this;
    }

    /**
     * Get model data as array
     * 
     * @return array
     */
    public function getData()
    {
        $data = array(
            'transactionId'      => isset($this->_transactionId) ? $this->_transactionId : null,
            'transactionShortId' => isset($this->_transactionShortId) ? $this->_transactionShortId : null,
            'subtype'            => isset($this->_subtype) ? $this->_subtype : null,
            'profileId'          => $this->_profileId,
            'securityCode'       => $this->_securityCode,
            'orderId'            => isset($this->_orderId) ? $this->_orderId : null,
            'systemId'           => !empty($this->_systemId) ? $this->_systemId : 'Not Available',
            'shopSystem'         => $this->_shopSystem,
            'moduleVersion'      => $this->_moduleVersion,
            'shopVersion'        => $this->_shopVersion,
            'deviceSite'         => $this->_deviceSite,
            'deviceToken'        => $this->_deviceToken
        );

        return $data;
    }

}
