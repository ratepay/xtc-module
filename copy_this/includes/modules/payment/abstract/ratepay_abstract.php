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

require_once(dirname(__FILE__) . '/../../../classes/ratepay/mappers/RequestMapper.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/services/RequestService.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/helpers/Data.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/helpers/Db.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/helpers/Session.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/helpers/Globals.php');

class ratepay_abstract
{

    /**
     * Payment code
     * 
     * @var string
     */
    public $code = 'ratepay_abstract';
    
    /**
     * Minimal order amount
     * 
     * @var float
     */
    public $minDe;
    
    /**
     * Maximal order amount
     * 
     * @var float
     */
    public $maxDe;
    
    /**
     * Minimal order amount
     * 
     * @var float
     */
    public $minAt;
    
    /**
     * Maximal order amount
     * 
     * @var float
     */
    public $maxAt;
    
    /**
     * b2b de flag
     * @var boolan
     */
    public $b2bDe = false;
    
    /**
     * b2b at flag
     * @var boolan
     */
    public $b2bAt = false;
    
    public $error = array();
    
    protected function _setCredentials($country)
    {
        switch (strtoupper($country)) {
            case 'AT':
                $this->profileId = constant('MODULE_PAYMENT_' . strtoupper($this->code) . '_PROFILE_ID_AT');
                $this->securityCode = constant('MODULE_PAYMENT_' . strtoupper($this->code) . '_SECURITY_CODE_AT');
                break;
            case 'DE':
                $this->profileId = constant('MODULE_PAYMENT_' . strtoupper($this->code) . '_PROFILE_ID_DE');
                $this->securityCode = constant('MODULE_PAYMENT_' . strtoupper($this->code) . '_SECURITY_CODE_DE');
                break;
            default:
                $this->profileId = null;
                $this->securityCode = null;
                break;
        }
    }


        /**
     * Retrieve payment selection array
     * 
     * @return array
     */
    public function selection()
    {
        global $order;
        
        $display = array(
            'id' => $this->code, 
            'module' => xtc_image(DIR_WS_IMAGES . '/' . $this->code . '_checkout_logo.png')
        );
        
        $neededFields = $this->_getNeededFields();
        if (!empty($neededFields)) {
            $display['fields'] = $neededFields;
        }
        
        $dob = rpDb::getCustomersDob(null, rpSession::getSessionEntry('customer_id'));
        
        if ($dob !== '0000-00-00' && !$this->_isAdult($dob)) {
            $display = null;
        }
        
        if ($order->billing['country']['iso_code_2'] != 'DE' && $order->billing['country']['iso_code_2'] != 'AT') {
            $display = null;
        }
        
        if (!rpData::isRatepayAvailable()) {
            $display = null;
        }
        
        $minVarName = 'min' . ucfirst(strtolower($order->billing['country']['iso_code_2']));
        $maxVarName = 'max' . ucfirst(strtolower($order->billing['country']['iso_code_2']));
        
        if ((floatval($order->info['total']) < floatval($this->$minVarName)) || (floatval($order->info['total']) > floatval($this->$maxVarName))) {
            $display = null;
        }
        
        $vatId = rpDb::getCustomersVatId(null, rpSession::getSessionEntry('customer_id'));
        $b2bVarName = 'b2b' .  ucfirst(strtolower($order->billing['country']['iso_code_2']));
        
        if (!$this->$b2bVarName && (!empty($order->customer['company']) || !empty($order->billing['company']) || !empty($vatId))) {
            $display = null;
        }
        
        if (sizeof($order->delivery) != sizeof($order->billing)) {
            $display = null;
        } else {
            if (is_array($order->billing)) {
                foreach ($order->billing as $key => $val) {
                    if ($order->billing[$key] != $order->delivery[$key]) {
                        $display = null;
                    }
                    unset($val);
                }
            }
        }

        return $display;
    }

    /**
     * Retrieve needed form fields for payment
     * 
     * @return array
     */
    protected function _getNeededFields()
    {
        $fields = array();
        $phone = $this->_getPhoneField();
        if (!empty($phone)) {
            $fields[] = $phone;
        }

        $dob = $this->_getDobField();
        if (!empty($dob)) {
            $fields[] = $dob;
        }

        $company = $this->_getCompanyField();
        if (!empty($company)) {
            $fields[] = $company;
        }

        $vatId = $this->_getVatIdField();
        if (!empty($vatId)) {
            $fields[] = $vatId;
        }
        
        return $fields;
    }

    /**
     * Retrieve phone form field
     * 
     * @return array
     */
    protected function _getPhoneField()
    {
        if ($this->_isPhoneNeeded()) {
            return array('title' => '', 'field' => '<div><small>Telefon</small></div>' . xtc_draw_input_field($this->code . '_phone', ''));
        }

        return null;
    }
    
    /**
     * Retrieve dob form field
     * 
     * @return array
     */
    protected function _getDobField()
    {
        if ($this->_isDobNeeded()) {
            return array(
                'title' => '',
                'field' => '<div><small>Geburtstag</small></div>' . xtc_draw_input_field($this->code . '_birthdate', '') . " " . constant(strtoupper($this->code) . "_VIEW_PAYMENT_BIRTHDATE_FORMAT")
            );
        }

        return null;
    }
    
    /**
     * Retrieve company form field
     * 
     * @return array
     */
    protected function _getCompanyField()
    {
        if ($this->_isCompanyNeeded()) {
            return array('title' => '', 'field' => '<div><small>Firma</small></div>' . xtc_draw_input_field($this->code . '_company', ''));
        }

        return null;
    }

    /**
     * Retrieve vat id form field
     * 
     * @return array
     */
    protected function _getVatIdField()
    {
        if ($this->_isVatIdNeeded()) {
            return array('title' => '', 'field' => '<div><small>Umsatzsteuer ID</small></div>' . xtc_draw_input_field($this->code . '_vatid', ''));
        }

        return null;
    }
    
    /**
     * Is phone needed
     * 
     * @return boolean
     */
    protected function _isPhoneNeeded()
    {
        global $order;
        return empty($order->customer['telephone']);
    }
    
    /**
     * Is dob needed
     * 
     * @return boolean
     */
    protected function _isDobNeeded()
    {
        $dob = rpDb::getCustomersDob(null, rpSession::getSessionEntry('customer_id'));
        return empty($dob) || $dob === '0000-00-00';
    }
    
    /**
     * Is company needed
     * 
     * @return boolean
     */
    protected function _isCompanyNeeded()
    {
        global $order;
        $vatId = rpDb::getCustomersVatId(null, rpSession::getSessionEntry('customer_id'));
        return (empty($order->customer['company']) || empty($order->billing['company'])) && !empty($vatId);
    }
    
    /**
     * Is vat id needed
     * 
     * @return boolean
     */
    protected function _isVatIdNeeded()
    {
        global $order;
        $vatId = rpDb::getCustomersVatId(null, rpSession::getSessionEntry('customer_id'));
        return (!empty($order->customer['company']) || !empty($order->billing['company'])) && empty($vatId);
    }
    
    /**
     * Is dob valid
     * 
     * @param string $dob
     * @return boolean
     */
    protected function _isDobValid($dob)
    {
        return is_numeric(substr(xtc_date_raw($dob), 4, 2)) && is_numeric(substr(xtc_date_raw($dob), 6, 2)) && is_numeric(substr(xtc_date_raw($dob), 0, 4));
    }
    
    /**
     * Check if the customer is over 18 years or redirect with an error message
     * 
     * @param string $dateStr 
     */
    protected function _isAdult($dateStr) 
    {
        $today = array();
        $geb = strval($dateStr);

        $gebtag = explode("-", $geb);
        $stampBirth = mktime(0, 0, 0, floatval($gebtag[1]), floatval($gebtag[2]), floatval($gebtag[0]));
        $today['day'] = date('d');
        $today['month'] = date('m');
        $today['year'] = date('Y') - 18;
        $stampToday = mktime(0, 0, 0, floatval($today['month']), floatval($today['day']), floatval($today['year']));
        if ($stampBirth > $stampToday) {
            return false;
        }
        
        return true;
    }

    /**
     * Is called after checkout_payment.php is confirmed,
     * checks if all needed customer data available or 
     * redirect the customer to the checkout_payment.php
     * with a error message otherwise the user get to the
     * ratepay terms page
     * 
     * @global order $order
     */
    public function pre_confirmation_check()
    {
        global $order;
        
        if (!rpGlobals::hasPostEntry($this->code . '_conditions')) {
            $this->error['CONDITIONS'] = 'MISSING';
        }
        
        if ($this->_isPhoneNeeded()) {
            if (rpGlobals::hasPostEntry($this->code . '_phone') && !rpData::betterEmpty(rpGlobals::getPostEntry($this->code . '_phone'))) {
                rpDb::setXtCustomerEntry(rpSession::getSessionEntry('customer_id'), 'customers_telephone', rpGlobals::getPostEntry($this->code . '_phone'));
                $order->customer['telephone'] = rpGlobals::getPostEntry($this->code . '_phone');
            } else {
                $this->error['PHONE'] = 'MISSING';
            }
        }
        
        if ($this->_isDobNeeded()) {
            if (rpGlobals::hasPostEntry($this->code . '_birthdate') && !rpData::betterEmpty(rpGlobals::getPostEntry($this->code . '_birthdate'))) {
                if (!$this->_isDobValid(rpGlobals::getPostEntry($this->code . '_birthdate'))) {
                    $this->error['DOB'] = 'INVALID';
                } else {
                    $dob = rpGlobals::getPostEntry($this->code . '_birthdate');
                    $dateStr = substr(xtc_date_raw($dob), 6, 2) . "." . substr(xtc_date_raw($dob), 4, 2) . "." . substr(xtc_date_raw($dob), 0, 4) . " 00:00:00";
                    $dateStr = substr(xtc_date_raw($dob), 0, 4) . '-' . substr(xtc_date_raw($dob), 4, 2) . '-' .  substr(xtc_date_raw($dob), 6, 2) . ' 00:00:00';
                    rpDb::setXtCustomerEntry(rpSession::getSessionEntry('customer_id'), 'customers_dob', $dateStr);
                }
            } else {
                $this->error['DOB'] = 'MISSING';
            }
        }

        if ($this->_isCompanyNeeded()) {
            if (rpGlobals::hasPostEntry($this->code . '_company') && !rpData::betterEmpty(rpGlobals::getPostEntry($this->code . '_company'))) {
                $company = rpGlobals::getPostEntry($this->code . '_company');
                $order->customer['company'] = $company;
                $order->billing['company']  = $company;
                $dbInput = xtc_db_input(rpDb::getXtCustomerEntry(rpSession::getSessionEntry('customer_id'), 'customers_default_address_id'));
                xtc_db_query("UPDATE " . TABLE_ADDRESS_BOOK . " " . "SET entry_company = '" . xtc_db_prepare_input($company) . "' " . "WHERE address_book_id = '" . $dbInput . "'");
            } else {
                $this->error['VATID'] = 'MISSING';
            }
        }

        if ($this->_isVatIdNeeded()) {
            if (rpGlobals::hasPostEntry($this->code . '_vatid') && !rpData::betterEmpty(rpGlobals::getPostEntry($this->code . '_vatid'))) {
                rpDb::setXtCustomerEntry(rpSession::getSessionEntry('customer_id'), 'customers_vat_id', rpGlobals::getPostEntry($this->code . '_vatid'));
            } else {
                $this->error['VATID'] = 'MISSING';
            }
        }

        if (!$this->_isAdult(rpDb::getCustomersDob(null, rpSession::getSessionEntry('customer_id')))) {
             $this->error['DOB'] = 'YOUNGER';
        }

        if (!empty($this->error)) {
            $error = urlencode($this->_getErrorString($this->error));
            $url = xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $error, 'SSL');
            xtc_redirect($url);
        }
    }
    
    /**
     * Place to put some JS validation for extra form field at checkout_payment.php
     */
    public function javascript_validation()
    {
        return false;
    }
    
    /**
     * Is called when the user clicks the "process button" but before the order is saved
     * here we send the PAYMENT_INIT and PAYMENT_REQUEST call to RatePAY in case of an 
     * we redirect the user to the checkout_payment.php with an error message
     */
    public function before_process() 
    {
        global $order;
        $result = $this->_paymentInit();
        if (!array_key_exists('error', $result) && array_key_exists('transactionId', $result)) {
            rpSession::setRpSessionEntry('transactionId', $result['transactionId']);
            rpSession::setRpSessionEntry('transactionShortId', $result['transactionShortId']);
            $result = $this->_paymentRequest($result['transactionId'], $result['transactionShortId']);
            if (array_key_exists('error', $result) && !array_key_exists('transactionId', $result)) {
                rpSession::cleanRpSession();
                rpData::disableRatepay();
                $error = urlencode(constant(strtoupper($this->code) . '_ERROR'));
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $error, 'SSL'));
            } else {
                rpSession::setRpSessionEntry('customers_country_code', $order->customer['country']['iso_code_2']);
                rpSession::setRpSessionEntry('descriptor', $result['descriptor']);
                rpSession::setRpSessionEntry('rpOrder', clone $order);
            }
        } else {
            rpData::disableRatepay();
            $error = urlencode(constant(strtoupper($this->code) . '_ERROR_GATEWAY'));
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $error, 'SSL'));
        }
    }

    /**
     * Is called when the order is saved to the db
     * here we send the PAYMENT_CONFIRM call to RatePAY
     */
    public function after_process() 
    {
        global $insert_id;
        $transactionId = rpSession::getRpSessionEntry('transactionId');
        $transactionShortId = rpSession::getRpSessionEntry('transactionShortId');
        if (!empty($transactionId)) {
            $result = $this->_paymentConfirm($transactionId, $transactionShortId, $insert_id);
            if (!array_key_exists('error', $result)) {
                $this->_saveRpOrder(rpSession::getRpSessionEntry('rpOrder'), $insert_id);
                $this->_setRatepayOrderPaid($insert_id);
                rpSession::cleanRpSession();
            } else {
                rpSession::cleanRpSession();
                $error = urlencode(constant(strtoupper($this->code) . '_ERROR'));
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $error, 'SSL'));
            }
        }
    }
    
    /**
     * @param string $insert_id
     */
    protected function _setRatepayOrderPaid($insert_id)
    {
        if (defined('MODULE_PAYMENT_' . strtoupper($this->code) . '_ORDER_STATUS_ID')) {
            $order_status_id = constant('MODULE_PAYMENT_' . strtoupper($this->code) . '_ORDER_STATUS_ID');
            xtc_db_query("UPDATE " . TABLE_ORDERS . " SET orders_status='" . $order_status_id . "' WHERE orders_id='" . $insert_id . "'");
        }
    }
    
    /**
     * Is called to render the process button
     */
    public function process_button() 
    {
        global $ot_coupon;
        rpSession::setRpSessionEntry('coupon', $ot_coupon->output);
    }
    
    /**
     * Retrieve any payment error
     *
     * @return array
     */
    function get_error() 
    {
        global $_GET;
        
        return array (
            'title' => 'RatePAY Error',
            'error' => urldecode($_GET['error_message'])
        );
    }
    
    /**
     * Save ratepay order wrapper
     * 
     * @param order $order
     * @param int $orderId
     */
    protected function _saveRpOrder(order $order, $orderId)
    {
        rpDb::setRatepayOrderData($order, $orderId);
    }

    /**
     * Build the constant for the error string 
     * and retrieve the error string
     * 
     * @param array $error
     * @return string
     */
    protected function _getErrorString(array $error)
    {
        $message = '';
        foreach ($error as $key => $value) {
            $message .= constant(strtoupper($this->code . '_' . $key) . '_IS_' . strtoupper($value));
        }
        
        return $message;
    }

    /**
     * Call payment init
     * 
     * @global order $order
     * @return array
     */
    protected function _paymentInit()
    {
        global $order;
        $data = array(
            'HeadInfo' => rpRequestMapper::getHeadInfoModel($order)
        );
        $requestService = new rpRequestService($this->sandbox, $data);
        $result = $requestService->callPaymentInit();
        rpDb::xmlLog($order, $requestService->getRequest(), 'N/A', $requestService->getResponse());
        return $result;
    }

    /**
     * Call PAYMENT_REQUEST request
     * 
     * @global order $order
     * @param string $transactionId
     * @param string$transactionShortId
     * @return array
     */
    protected function _paymentRequest($transactionId, $transactionShortId)
    {
        global $order;
        
        rpSession::setRpSessionEntry('countryCode', $order->customer['country']['iso_code_2']);
        
        $data = array(
            'HeadInfo' => rpRequestMapper::getHeadInfoModel($order, $transactionId, $transactionShortId),
            'CustomerInfo' => rpRequestMapper::getCustomerInfoModel($order),
            'BasketInfo' => rpRequestMapper::getBasketInfoModel($order),
            'PaymentInfo' => rpRequestMapper::getPaymentInfoModel($order)
        );
        $requestService = new rpRequestService($this->sandbox, $data);
        $result = $requestService->callPaymentRequest();
        rpDb::xmlLog($order, $requestService->getRequest(), 'N/A', $requestService->getResponse());
        return $result;
    }

    /**
     * Call PAYMENT_CONFIRM request
     * 
     * @global order $order
     * @param string $transactionId
     * @param string $transactionShortId
     * @param int $orderId
     * @return array
     */
    protected function _paymentConfirm($transactionId, $transactionShortId, $orderId)
    {
        global $order;
        $data = array(
            'HeadInfo' => rpRequestMapper::getHeadInfoModel($order, $transactionId, $transactionShortId, $orderId)
        );
        
        $requestService = new rpRequestService($this->sandbox, $data);
        $result = $requestService->callPaymentConfirm();
        rpDb::xmlLog($order, $requestService->getRequest(), $orderId, $requestService->getResponse());
        return $result;
    }
    
    protected function _installRatepayPaidState()
    {
        $check_query = xtc_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'RatePAY [Bezahlt]' limit 1");

        if (xtc_db_num_rows($check_query) < 1) {
            $status_query = xtc_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
            $status = xtc_db_fetch_array($status_query);

            $status_id = $status['status_id'] + 1;

            $languages = xtc_get_languages();

            foreach ($languages as $lang) {
                xtc_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $lang['id'] . "', 'RatePAY [Bezahlt]')");
            }

            $flags_query = xtc_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
            if (xtc_db_num_rows($flags_query) == 1) {
                xtc_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 0 and downloads_flag = 0 where orders_status_id = '" . $status_id . "'");
            }
        } else {
            $check = xtc_db_fetch_array($check_query);

            $status_id = $check['orders_status_id'];
        }

        return $status_id;
    }
}
