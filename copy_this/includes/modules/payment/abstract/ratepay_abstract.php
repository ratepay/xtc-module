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
     * Min order amount
     * 
     * @var float
     */
    public $min;
    
    /**
     * Max order amount
     * 
     * @var float
     */
    public $max;

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
        
        if ($order->billing['country']['iso_code_2'] != 'DE') {
            $display = null;
        }
        
        if (!Data::isRatepayAvailable()) {
            $display = null;
        }
        
        if ((floatval($order->info['total']) < floatval($this->min)) || (floatval($order->info['total']) > floatval($this->max))) {
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

        $this->setInfoVisited(false);

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
        
        $jsFunctions = file_get_contents(DIR_FS_CATALOG . 'templates/javascript/ratepay_checkout.js');
        $js = 'window.onload = RpCheckout.ratepayOnLoad;';
        $fields[] = array('title' => '', 'field' => sprintf('<script type="text/javascript">%s</script>', $jsFunctions));
        $fields[] = array('title' => '', 'field' => sprintf('<script type="text/javascript">%s</script>', $js));
        
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
            return array('title' => 'Telefon:', 'field' => xtc_draw_input_field($this->code . '_phone', ''));
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
                'title' => 'Geburtstag:',
                'field' => xtc_draw_input_field($this->code . '_birthdate', '') . " " . constant(strtoupper($this->code) . "_VIEW_PAYMENT_BIRTHDATE_FORMAT")
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
            return array('title' => 'Firma:', 'field' => xtc_draw_input_field($this->code . '_company', ''));
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
            return array('title' => 'Umsatzsteuer ID:', 'field' => xtc_draw_input_field($this->code . '_vatid', ''));
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
        $dob = Db::getCustomersDob(null, Session::getSessionEntry('customer_id'));
        return empty($dob);
    }
    
    /**
     * Is company needed
     * 
     * @return boolean
     */
    protected function _isCompanyNeeded()
    {
        global $order;
        $vatId = Db::getCustomersVatId(null, Session::getSessionEntry('customer_id'));
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
        $vatId = Db::getCustomersVatId(null, Session::getSessionEntry('customer_id'));
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
    protected function _isAdult($dateStr) {
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
        $error = array();
        global $order;
        if (!$this->isInfoVisited()) {
            if ($this->_isPhoneNeeded()) {
                if (Globals::hasPostEntry($this->code . '_phone') && !Data::betterEmpty(Globals::getPostEntry($this->code . '_phone'))) {
                    Db::setXtCustomerEntry(Session::getSessionEntry('customer_id'), 'customers_telephone', Globals::getPostEntry($this->code . '_phone'));
                    $order->customer['telephone'] = Globals::getPostEntry($this->code . '_phone');
                } else {
                    $error['PHONE'] = 'MISSING';
                }
            }
            
            if ($this->_isDobNeeded()) {
                if (Globals::hasPostEntry($this->code . '_birthdate') && !Data::betterEmpty(Globals::getPostEntry($this->code . '_birthdate'))) {
                    if (!$this->_isDobValid(Globals::getPostEntry($this->code . '_birthdate'))) {
                        $error['DOB'] = 'INVALID';
                    } else {
                        $dob = Globals::getPostEntry($this->code . '_birthdate');
                        $dateStr = substr(xtc_date_raw($dob), 6, 2) . "." . substr(xtc_date_raw($dob), 4, 2) . "." . substr(xtc_date_raw($dob), 0, 4) . " 00:00:00";
                        Db::setXtCustomerEntry(Session::getSessionEntry('customer_id'), 'customers_dob', $dateStr);
                    }
                } else {
                    $error['DOB'] = 'MISSING';
                }
            }
            
            if ($this->_isCompanyNeeded()) {
                if (Globals::hasPostEntry($this->code . '_company') && !Data::betterEmpty(Globals::getPostEntry($this->code . '_company'))) {
                    $company = Globals::getPostEntry($this->code . '_company');
                    $order->customer['company'] = $company;
                    $order->billing['company']  = $company;
                    $dbInput = xtc_db_input(Db::getXtCustomerEntry(Session::getSessionEntry('customer_id'), 'customers_default_address_id'));
                    xtc_db_query("UPDATE " . TABLE_ADDRESS_BOOK . " "
                               . "SET entry_company = '" . xtc_db_prepare_input($company) . "' "
                               . "WHERE address_book_id = '" . $dbInput . "'"
                    );

                } else {
                    $error['VATID'] = 'MISSING';
                }
            }
            
            if ($this->_isVatIdNeeded()) {
                if (Globals::hasPostEntry($this->code . '_vatid') && !Data::betterEmpty(Globals::getPostEntry($this->code . '_vatid'))) {
                    Db::setXtCustomerEntry(Session::getSessionEntry('customer_id'), 'customers_vat_id', Globals::getPostEntry($this->code . '_vatid'));
                } else {
                    $error['VATID'] = 'MISSING';
                }
            }
            
            if (!$this->_isAdult(Db::getCustomersDob(null, Session::getSessionEntry('customer_id')))) {
                 $error['DOB'] = 'YOUNGER';
            }
            
            if (empty($error)) {
                $this->setInfoVisited(true);
                Session::setRpSessionEntry('basketAmount', Data::getBasketAmount($order));
                $url = xtc_href_link($this->code . "_checkout_terms.php", '', 'SSL');
            } else {
                $error = urlencode($this->_getErrorString($error));
                $url = xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $error, 'SSL');
            }
            
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
            Session::setRpSessionEntry('transactionId', $result['transactionId']);
            Session::setRpSessionEntry('transactionShortId', $result['transactionShortId']);
            $result = $this->_paymentRequest($result['transactionId'], $result['transactionShortId']);
            if (array_key_exists('error', $result) && !array_key_exists('transactionId', $result)) {
                Session::cleanRpSession();
                $error = urlencode(constant(strtoupper($this->code) . '_ERROR_GATEWAY'));
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $error, 'SSL'));
            } else {
                Session::setRpSessionEntry('customers_country_code', $order->customer['country']['iso_code_2']);
                Session::setRpSessionEntry('descriptor', $result['descriptor']);
            }
        } else {
            $error = urlencode(constant(strtoupper($this->code) . '_ERROR'));
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $error, 'SSL'));
        }
    }

    /**
     * Is called when the order is saved to the db
     * here we send the PAYMENT_CONFIRM call to RatePAY
     */
    public function after_process() 
    {
        global $insert_id, $order;
        $transactionId = Session::getRpSessionEntry('transactionId');
        $transactionShortId = Session::getRpSessionEntry('transactionShortId');
        if (!empty($transactionId)) {
            $result = $this->_paymentConfirm($transactionId, $transactionShortId, $insert_id);
            if (!array_key_exists('error', $result)) {
                $this->_saveRpOrder($order, $insert_id);
                Session::cleanRpSession();
            } else {
                Session::cleanRpSession();
                $error = urlencode(constant(strtoupper($this->code) . '_ERROR'));
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $error, 'SSL'));
            }
        }
    }
    
    /**
     * Is called to render the process button
     */
    public function process_button() 
    {
        global $ot_coupon;
        Session::setRpSessionEntry('coupon', $ot_coupon->output);
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
        Db::setRatepayOrderData($order, $orderId);
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
            'HeadInfo' => RequestMapper::getHeadInfoModel($order)
        );
        $requestService = new RequestService($this->sandbox, $data);
        $result = $requestService->callPaymentInit();
        Db::xmlLog($order, $requestService->getRequest(), 'N/A', $requestService->getResponse());
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
        $data = array(
            'HeadInfo' => RequestMapper::getHeadInfoModel($order, $transactionId, $transactionShortId),
            'CustomerInfo' => RequestMapper::getCustomerInfoModel($order),
            'BasketInfo' => RequestMapper::getBasketInfoModel($order),
            'PaymentInfo' => RequestMapper::getPaymentInfoModel($order)
        );
        $requestService = new RequestService($this->sandbox, $data);
        $result = $requestService->callPaymentRequest();
        Db::xmlLog($order, $requestService->getRequest(), 'N/A', $requestService->getResponse());
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
            'HeadInfo' => RequestMapper::getHeadInfoModel($order, $transactionId, $transactionShortId, $orderId)
        );
        $requestService = new RequestService($this->sandbox, $data);
        $result = $requestService->callPaymentConfirm();
        Db::xmlLog($order, $requestService->getRequest(), $orderId, $requestService->getResponse());
        return $result;
    }

    /**
     * Set info page visited
     * 
     * @param boolean $visited
     */
    public function setInfoVisited($visited)
    {
        Session::setRpSessionEntry('infoVisited', $visited);
    }

    /**
     * is info page visited 
     * 
     * @return boolean
     */
    protected function isInfoVisited()
    {
        return Session::getRpSessionEntry('infoVisited');
    }

}
