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
 * @package   PayIntelligent_Ratepay
 * @copyright (C) 2010 PayIntelligent GmbH  <http://www.payintelligent.de/>
 * @license   GPLv2
 * */
class pi_ratepay_rate {

    //Begin default
    var $code;
    var $title;
    var $public_title;
    var $description;
    var $enabled;
    var $_check;
    //End default

    var $profileId;
    var $securityCode;
    var $sandbox;
    var $logs;
    var $gtcURL;
    var $privacyURL;
    var $merchantPrivacyURL;
    var $merchantName;
    var $bankName;
    var $sortCode;
    var $accountNr;
    var $swift;
    var $iban;
    var $email;
    var $extraField;
    var $min;
    var $max;
    var $owner;
    var $hr;
    var $court;
    var $fon;
    var $fax;
    var $street;
    var $plz;
    var $descriptor;
    var $transId;
    var $transShortId;
    var $pi_ot_coupon;
    var $piCouponIncTax;
    var $piReCalculateTax;

    /**
     * This constructor set's all properties for the pi_ratepay_rate object
     */
    function pi_ratepay_rate() {
        global $order;
        //Begin default
        $this->code = 'pi_ratepay_rate';
        $this->title = MODULE_PAYMENT_PI_RATEPAY_RATE_TEXT . "(1.0.0)";
        $this->public_title = MODULE_PAYMENT_PI_RATEPAY_RATE_TEXT_TITLE;
        $this->description = utf8_decode(MODULE_PAYMENT_PI_RATEPAY_RATE_TEXT_DESCRIPTION);
        $this->enabled = ((MODULE_PAYMENT_PI_RATEPAY_RATE_STATUS == 'True') ? true : false);
        //End default
        //Begin custom
        $this->profileId = MODULE_PAYMENT_PI_RATEPAY_RATE_PROFILE_ID;
        $this->securityCode = MODULE_PAYMENT_PI_RATEPAY_RATE_SECURITY_CODE;
        $this->sandbox = ((MODULE_PAYMENT_PI_RATEPAY_RATE_SANDBOX == 'True') ? true : false);
        $this->logs = ((MODULE_PAYMENT_PI_RATEPAY_RATE_LOGS == 'True') ? true : false);
        $this->gtcURL = MODULE_PAYMENT_PI_RATEPAY_RATE_GTC;
        $this->privacyURL = MODULE_PAYMENT_PI_RATEPAY_RATE_PRIVACY;
        $this->merchantPrivacyURL = MODULE_PAYMENT_PI_RATEPAY_RATE_MERCHANT_PRIVACY;
        $this->merchantName = MODULE_PAYMENT_PI_RATEPAY_RATE_MERCHANT_NAME;
        $this->bankName = MODULE_PAYMENT_PI_RATEPAY_RATE_BANK_NAME;
        $this->sortCode = MODULE_PAYMENT_PI_RATEPAY_RATE_SORT_CODE;
        $this->accountNr = MODULE_PAYMENT_PI_RATEPAY_RATE_ACCOUNT_NR;
        $this->swift = MODULE_PAYMENT_PI_RATEPAY_RATE_SWIFT;
        $this->iban = MODULE_PAYMENT_PI_RATEPAY_RATE_IBAN;
        $this->email = MODULE_PAYMENT_PI_RATEPAY_RATE_EMAIL;
        $this->extraField = MODULE_PAYMENT_PI_RATEPAY_RATE_EXTRA_FIELD;
        $this->min = MODULE_PAYMENT_PI_RATEPAY_RATE_MIN;
        $this->max = MODULE_PAYMENT_PI_RATEPAY_RATE_MAX;

        $this->owner = MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_OWNER;
        $this->hr = MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_HR;
        $this->court = MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_COURT;
        $this->fon = MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_FON;
        $this->fax = MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_FAX;
        $this->street = MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_STREET;
        $this->plz = MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_PLZ;
        $this->piCouponIncTax = MODULE_ORDER_TOTAL_COUPON_INC_TAX;
        $this->piReCalculateTax = MODULE_ORDER_TOTAL_COUPON_CALC_TAX;
        //End custom

        $this->sort_order = MODULE_PAYMENT_PI_RATEPAY_RATE_SORT_ORDER;

        if ((int) MODULE_PAYMENT_PI_RATEPAY_RATE_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_PI_RATEPAY_RATE_ORDER_STATUS_ID;
        }
        $this->check();
        if (is_object($order)) {
            $this->update_status();
        }
    }

    /**
     * Updates the Status
     */
    function update_status() {
        global $order;
        if (($this->enabled == true) && ((int) MODULE_PAYMENT_PI_RATEPAY_RATE_ZONE > 0)) {
            $check_flag = false;
            $check_query = xtc_db_query("SELECT zone_id from "
                    . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '"
                    . MODULE_PAYMENT_PI_RATEPAY_RATE_ZONE . "' and zone_country_id = '"
                    . xtc_db_input($order->billing['country']['id']) . "' order by zone_id");

            while ($check = xtc_db_fetch_array($check_query)) {
                if ($check['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check['zone_id'] == $order->billing['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }

            if ($check_flag == false) {
                $this->enabled = false;
            }
        }
    }

    /*
     * Javascript Validation
     *
     * @return boolean
     *
     */

    function javascript_validation() {
        return false;
    }

    /**
     * This function checks whether to display RatePAY Rate or not
     *
     * @return boolean
     */
    function selection() {
        global $order;

        unset($_SESSION['pi']['confirm']);
        unset($_SESSION['pi']['coupon']);

        $customerId = $_SESSION ['customer_id'];

        $query = xtc_db_query("SELECT customers_gender, customers_dob, customers_email_address, customers_telephone, customers_fax, customers_vat_id from " . TABLE_CUSTOMERS . " WHERE customers_id ='" . xtc_db_input($customerId) . "' ");
        $customerXTC = xtc_db_fetch_array($query);

        $fieldsBool = false;

        $fields = array();

        if ($customerXTC['customers_telephone'] == '') {
            $fieldsBool = true;
            $fields[] = array('title' => 'Telefon', 'field' => xtc_draw_input_field('pi_phone_rate', ''));
        }

        if ($customerXTC['customers_dob'] == '0000-00-00 00:00:00') {
            $fieldsBool = true;
            $fields[] = array('title' => 'Geburtstag', 'field' => xtc_draw_input_field('pi_birthdate_rate', '') . " " . PI_RATEPAY_RATE_VIEW_PAYMENT_BIRTHDATE_FORMAT);
        }

        if (($order->customer['company'] != '' || $order->billing['company'] != '') && $customerXTC['customers_vat_id'] == '') {
            $fieldsBool = true;
            $fields[] = array('title' => PI_RATEPAY_RATE_VIEW_PAYMENT_VATID, 'field' => xtc_draw_input_field('pi_vatid_rate', ''));
        }

        if ($order->customer['company'] == '' && $order->billing['company'] == '' && $customerXTC['customers_vat_id'] != '') {
            $fieldsBool = true;
            $fields[] = array('title' => PI_RATEPAY_RATE_VIEW_PAYMENT_COMPANY, 'field' => xtc_draw_input_field('pi_company_rate', ''));
        }

        if ($fieldsBool) {
            $display = array('id' => $this->code, 'module' => xtc_image(DIR_WS_IMAGES . '/pi_ratepay_rate_checkout_logo.png'), 'fields' => $fields);
        } else {
            $display = array('id' => $this->code, 'module' => xtc_image(DIR_WS_IMAGES . '/pi_ratepay_rate_checkout_logo.png'));
        }

        $customer_country = $order->customer['country']['iso_code_2'];

        $currency = $_SESSION ['currency'];

        if ($customer_country != "DE") {
            $display = null;
        }

        //Check allowed currency
        if (strtoupper($currency) != "EUR") {
            $display = null;
        }
        //Compare billing and delivery address
        if (sizeof($order->delivery) != sizeof($order->billing)) {
            $display = null;
        } else {
            if (is_array($order->billing)) {
                foreach ($order->billing as $key => $val) {
                    if ($order->billing[$key] != $order->delivery[$key]) {
                        $display = null;
                    }
                }
            }
        }
        if (isset($_SESSION['disable'])) {
            if ($_SESSION['disable'] == true) {
                $display = null;
            }
        }

        if ($customerXTC['customers_dob'] != '0000-00-00 00:00:00') {
            $geb = strval($customerXTC['customers_dob']);
            $gebtag = explode("-", $geb);
            // explode day form time (14 00:00:00)
            $birthDay = explode(" ", $gebtag[2]);

            $stampBirth = mktime(0, 0, 0, $gebtag[1], $birthDay[0], $gebtag[0]);
            $result['stampBirth'] = $stampBirth;
            // fetch the current date (minus 18 years)
            $today['day'] = date('d');
            $today['month'] = date('m');
            $today['year'] = date('Y') - 18;

            // generate todays timestamp
            $stampToday = mktime(0, 0, 0, $today['month'], $today['day'], $today['year']);
            $result['$stampToday'] = $stampToday;
            $flag = false;
            if ($stampBirth > $stampToday) {
                $display = null;
            }
        }
        $order_total = $order->info['total'];
        $_SESSION['pi_ratepay_rate_order_total'] = ($order_total + $this->getShippingTaxAmount($order));
        $min_order = $this->min;
        $max_order = $this->max;
        //Check minimum order size and maximum order size
        if ((floatval($order_total) < floatval($min_order)) || (floatval($order_total) > floatval($max_order))) {
            $display = null;
        }
        return $display;
    }
    
    /**
     * Check if the customer is over 18 years or redirect with an error message
     * 
     * @param string $dateStr 
     */
    function verifyAge($dateStr) {
        $today = array();
        $geb = strval($dateStr);

        $gebtag = explode(".", $geb);
        $birthDay = explode(" ", $gebtag[2]);

        $stampBirth = mktime(0, 0, 0, $gebtag[1], $gebtag[0], $birthDay[0]);
        $today['day'] = date('d');
        $today['month'] = date('m');
        $today['year'] = date('Y') - 18;
        $stampToday = mktime(0, 0, 0, $today['month'], $today['day'], $today['year']);

        if ($stampBirth > $stampToday) {
            $errorStr = urlencode(PI_RATEPAY_RATE_ERROR_AGE);
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $errorStr, 'SSL'));
        }
    }

    /**
     * Checks if all needed Data is set and initializes RatePAY Payment
     *
     * @return boolean
     */
    function pre_confirmation_check() {
        global $order;
        if (isset($_SESSION['pi_ratepay_rate_conditions']) && $_SESSION['pi_ratepay_rate_conditions'] == true) {
            unset($_SESSION['pi_ratepay_rate_conditions']);

            $response = $this->paymentInit($order);
            if ($response) {
                if ((string) $response->head->processing->status->attributes()->code == "OK" && (string) $response->head->processing->result->attributes()->code == "350") {
                    return false;
                } else {
                    $errorStr = urlencode(PI_RATEPAY_RATE_ERROR_GATEWAY);
                    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $errorStr, 'SSL'));
                }
            } else {
                $_SESSION['disable'] = true;
                $errorStr = urlencode(PI_RATEPAY_RATE_ERROR);
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $errorStr, 'SSL'));
            }
        } else {
            $successFon = false;
            $successDate = false;
            $inputNeededFon = false;
            $inputNeededBirthdate = false;
            if (isset($_POST['pi_phone_rate']) && isset($_POST['pi_birthdate_rate'])) {

                $inputNeededFon = true;
                $inputNeededBirthdate = true;

                if ($_POST['pi_phone_rate'] != '') {
                    $successFon = true;
                    $customerId = $_SESSION['customer_id'];
                    xtc_db_query("update " . TABLE_CUSTOMERS . " set customers_telephone = '" . xtc_db_prepare_input($_POST['pi_phone_rate']) . "' WHERE customers_id ='" . xtc_db_input($customerId) . "' ");
                }

                $dob = xtc_db_prepare_input($_POST['pi_birthdate_rate']);
                if (is_numeric(substr(xtc_date_raw($dob), 4, 2)) && is_numeric(substr(xtc_date_raw($dob), 6, 2)) && is_numeric(substr(xtc_date_raw($dob), 0, 4))) {
                    if (checkdate(substr(xtc_date_raw($dob), 4, 2), substr(xtc_date_raw($dob), 6, 2), substr(xtc_date_raw($dob), 0, 4))) {
                        $successDate = true;
                        $customerId = $_SESSION['customer_id'];
                        $dateStr = substr(xtc_date_raw($dob), 6, 2) . "." . substr(xtc_date_raw($dob), 4, 2) . "." . substr(xtc_date_raw($dob), 0, 4) . " 00:00:00";
                        xtc_db_query("update " . TABLE_CUSTOMERS . " set customers_dob = '" . xtc_date_raw($dateStr) . "' WHERE customers_id ='" . xtc_db_input($customerId) . "' ");
                        $this->verifyAge($dateStr);
                    }
                }
            } else if (isset($_POST['pi_phone_rate'])) {
                $inputNeededFon = true;

                if ($_POST['pi_phone_rate'] != '') {
                    $successFon = true;
                    $customerId = $_SESSION['customer_id'];
                    xtc_db_query("update " . TABLE_CUSTOMERS . " set customers_telephone = '" . xtc_db_prepare_input($_POST['pi_phone_rate']) . "' WHERE customers_id ='" . xtc_db_input($customerId) . "' ");
                }
            } else if (isset($_POST['pi_birthdate_rate'])) {
                $inputNeededBirthdate = true;

                $dob = xtc_db_prepare_input($_POST['pi_birthdate_rate']);

                if (is_numeric(substr(xtc_date_raw($dob), 4, 2)) && is_numeric(substr(xtc_date_raw($dob), 6, 2)) && is_numeric(substr(xtc_date_raw($dob), 0, 4))) {
                    if (checkdate(substr(xtc_date_raw($dob), 4, 2), substr(xtc_date_raw($dob), 6, 2), substr(xtc_date_raw($dob), 0, 4))) {
                        $successDate = true;
                        $customerId = $_SESSION['customer_id'];
                        $dateStr = substr(xtc_date_raw($dob), 6, 2) . "." . substr(xtc_date_raw($dob), 4, 2) . "." . substr(xtc_date_raw($dob), 0, 4) . " 00:00:00";
                        xtc_db_query("update " . TABLE_CUSTOMERS . " set customers_dob = '" . xtc_date_raw($dateStr) . "' WHERE customers_id ='" . xtc_db_input($customerId) . "' ");
                        $this->verifyAge($dateStr);
                    }
                }
            }

            $customerId = $_SESSION['customer_id'];
            $query = xtc_db_query("SELECT customers_gender, customers_dob, customers_email_address, customers_telephone, customers_fax, customers_vat_id, customers_default_address_id from " . TABLE_CUSTOMERS . " WHERE customers_id ='" . xtc_db_input($customerId) . "' ");
            $customerXTC = xtc_db_fetch_array($query);

            if (($customerXTC['customers_vat_id'] == '' && ($order->customer['company'] != '' || $order->billing['company'] != '')) || ($customerXTC['customers_vat_id'] != '' && ($order->customer['company'] == '' || $order->billing['company'] == ''))) {
                $_SESSION['pi']['vatid'] = $customerXTC['customers_vat_id'];
                if ($customerXTC['customers_vat_id'] == '') {
                    if ($_POST['pi_vatid_rate'] != '') {
                        $_SESSION['pi']['vatid'] = $_POST['pi_vatid_rate'];
                        xtc_db_query("update " . TABLE_CUSTOMERS . " set customers_vat_id = '" . xtc_db_prepare_input($_SESSION['pi']['vatid']) . "' WHERE customers_id ='" . xtc_db_input($customerId) . "' ");
                    } else {
                        $errorStr = urlencode(PI_RATEPAY_RATE_ERROR_VATID_ERROR);
                        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $errorStr, 'SSL'));
                    }
                }

                if ($order->customer['company'] == '') {
                    if ($_POST['pi_company_rate'] != '') {
                        $_SESSION['pi']['company'] = $_POST['pi_company_rate'];
                        $order->customer['company'] = $_POST['pi_company_rate'];
                        xtc_db_query("update " . TABLE_ADDRESS_BOOK . " set entry_company = '" . xtc_db_prepare_input($_SESSION['pi']['company']) . "' WHERE address_book_id ='" . xtc_db_input($customerXTC['customers_default_address_id']) . "' ");
                    } else {
                        $errorStr = urlencode(PI_RATEPAY_RATE_ERROR_COMPANY_ERROR);
                        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $errorStr, 'SSL'));
                    }
                }
            }

            if ($inputNeededFon == true && $inputNeededBirthdate == true) {
                if ($successDate) {
                    $this->verifyAge($dateStr);
                }

                if ($successFon == true && $successDate == true) {
                    xtc_redirect(xtc_href_link("pi_ratepay_rate_checkout_terms.php", '', 'SSL'));
                } else {
                    if ($successFon == false && $successDate == false) {
                        $errorStr = urlencode(PI_RATEPAY_RATE_ERROR_PHONE_AND_BIRTH);
                        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $errorStr, 'SSL'));
                    } else if ($successDate == false) {
                        $errorStr = urlencode(PI_RATEPAY_RATE_ERROR_BIRTH);
                        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $errorStr, 'SSL'));
                    } else if ($successFon == false) {
                        $errorStr = urlencode(PI_RATEPAY_RATE_ERROR_PHONE);
                        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $errorStr, 'SSL'));
                    }
                }
            } else if ($inputNeededFon) {
                if ($successFon) {
                    xtc_redirect(xtc_href_link("pi_ratepay_rate_checkout_terms.php", '', 'SSL'));
                } else {
                    $errorStr = urlencode(PI_RATEPAY_RATE_ERROR_PHONE);
                    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $errorStr, 'SSL'));
                }
            } else if ($inputNeededBirthdate) {
                if ($successDate) {
                    xtc_redirect(xtc_href_link("pi_ratepay_rate_checkout_terms.php", '', 'SSL'));
                } else {
                    $errorStr = urlencode(PI_RATEPAY_RATE_ERROR_BIRTH);
                    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $errorStr, 'SSL'));
                }
            } else {
                xtc_redirect(xtc_href_link("pi_ratepay_rate_checkout_terms.php", '', 'SSL'));
            }
        }
        return false;
    }

    function confirmation() {
        return false;
    }

    /*
     * This method creates the String for the process button
     *
     * @return String
     */

    function process_button() {
        global $HTTP_POST_VARS, $order, $xtPrice;
        $_SESSION['pi']['coupon'] = $GLOBALS['ot_coupon']->output;

        $payment_type = 'RATEPAY';

        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
            $this->amount = $order->info['total'] + $order->info['tax'];
        } else {
            $this->amount = $order->info['total'];
        }

        $this->amount = round($xtPrice->xtcCalculateCurrEx($this->amount, $_SESSION['currency']), $xtPrice->get_decimal_places($_SESSION['currency']));
        $this->amount = number_format($this->amount, 2, '.', '');

        $currency = $_SESSION ['currency'];


        $process_button_string = xtc_draw_hidden_field('paymentType', $payment_type);


        return $process_button_string;
    }

    /*
     * Requests the Payment requests and handles the response.
     *
     * @return boolean
     */

    function before_process() {
        global $HTTP_POST_VARS, $order, $xtPrice;
        global $language;
        $orderId = $_SESSION['success_order_id'];
        if ($orderId == '') {
            $orderId = 'n/a';
        }
        $transactionId = $_SESSION['pi']['tid'];

        $return = $this->paymentRequest($order, $xtPrice);

        $request = $return[0];
        $response = $return[1];
        $first_name = $order->delivery['firstname'];
        $last_name = $order->delivery['lastname'];

        if ($response) {
            $this->piRatepayLog($orderId, $transactionId, 'PAYMENT_REQUEST', 'n/a', $request, $response, $first_name, $last_name);
            if ((string) $response->head->processing->status->attributes()->code == "OK" && (string) $response->head->processing->result->attributes()->code == "402") {
                $this->descriptor = (string) $response->content->payment->descriptor;
                $this->transId = (string) $response->head->{'transaction-id'};
                $this->transShortId = (string) $response->head->{'transaction-short-id'};
            } else {
                $_SESSION['disable'] = true;
                $errorStr = urlencode(PI_RATEPAY_RATE_ERROR);
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $errorStr, 'SSL'));
            }
        } else {
            $_SESSION['disable'] = true;
            $this->piRatepayLog($orderId, $transactionId, 'PAYMENT_REQUEST', 'n/a', $request, false, $first_name, $last_name);
            $errorStr = urlencode(PI_RATEPAY_RATE_ERROR);
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $errorStr, 'SSL'));
        }
        return false;
    }

    /**
     * Confirm of the order and requesting RatePAY Confirm and handles the Response and saving all necessary Data to DB
     *
     */
    function after_process() {
        unset($_SESSION['pi']['company']);
        unset($_SESSION['pi']['vatid']);
        global $HTTP_POST_VARS, $order, $xtPrice, $insert_id;
        global $language;
        include_once(DIR_WS_CLASSES . 'pi_order.php');
        $neworder = new pi_order($insert_id);
        $return = $this->paymentConfirm($insert_id);

        $request = $return[0];
        $response = $return[1];
        $orderId = $insert_id;
        $first_name = $order->delivery['firstname'];
        $last_name = $order->delivery['lastname'];
        if ($response) {
            $transactionId = $this->transId;
            $transactionShortId = $this->transShortId;
            $this->piRatepayLog($orderId, $transactionId, 'PAYMENT_CONFIRM', 'n/a', $request, $response, $first_name, $last_name);

            if ((string) $response->head->processing->status->attributes()->code == "OK" && (string) $response->head->processing->result->attributes()->code == "400") {


                $id = $insert_id;

                $sql = "INSERT INTO pi_ratepay_rate_orders (order_number, transaction_id, transaction_short_id, descriptor)
								VALUES ('" . xtc_db_input($id) . "', '" . xtc_db_input($transactionId) . "', '" . xtc_db_input($transactionShortId) . "','" . xtc_db_input($this->descriptor) . "')";

                xtc_db_query($sql);

                for ($i = 0; $i <= sizeof($neworder->products); $i++) {

                    $attributes = "";

                    if (isset($neworder->products[$i]['attributes'])) {
                        foreach ($neworder->products[$i]['attributes'] as $attr) {
                            $attributes = $attributes . ", " . $attr['option'] . ": " .
                                    $attr['value'];
                        }
                    }
                    $name = strip_tags($neworder->products[$i]['name'] . $attributes);

                    $price = round($neworder->products[$i]['price'], $xtPrice->get_decimal_places($currency));
                    $qty = intval($neworder->products[$i]['qty']);
                    if ($price > 0) {
                        $sql = "INSERT INTO pi_ratepay_rate_orderdetails (order_number,article_number, real_article_number, article_name,ordered,article_netUnitPrice)
										VALUES ('" . xtc_db_input($id) . "', '" . xtc_db_input($neworder->products[$i]['opid']) . "', '" . xtc_db_input(xtc_get_prid($neworder->products[$i]['id'])) . "','" . xtc_db_input($name) . "', " . xtc_db_input($qty) . ", " . number_format($price, 2) . ")";

                        xtc_db_query($sql);
                    }
                }

                if (isset($_SESSION['pi_ratepay']['shipping'])) {
                    $shippingCost = $_SESSION['pi_ratepay']['shipping'];
                    $sql = "INSERT INTO pi_ratepay_rate_orderdetails (order_number,article_number,real_article_number,article_name,ordered,article_netUnitPrice)
									VALUES ('" . xtc_db_input($id) . "', 'SHIPPING', 'SHIPPING', 'Versand', 1, " . number_format($shippingCost, 2, ".", "") . ")";
                    xtc_db_query($sql);
                    unset($_SESSION['pi_ratepay']['shipping']);
                }
                if (isset($_SESSION['pi_ratepay']['discount'])) {
                    $discount_price = $_SESSION['pi_ratepay']['discount'] * -1;
                    $sql = "INSERT INTO pi_ratepay_rate_orderdetails (order_number,article_number, real_article_number, article_name,ordered,article_netUnitPrice)
											VALUES ('" . xtc_db_input($id) . "', 'DISCOUNT', 'DISCOUNT', 'Rabatt', 1, " . number_format($discount_price, 2) . ")";
                    xtc_db_query($sql);
                    unset($_SESSION['pi_ratepay']['discount']);
                }
                if (empty($_SESSION['pi']['coupon']) == false) {
                    foreach ($_SESSION['pi']['coupon'] as $value) {
                        $sql = "INSERT INTO pi_ratepay_rate_orderdetails (order_number,article_number, real_article_number, article_name,ordered,article_netUnitPrice)
											VALUES ('" . xtc_db_input($id) . "', 'COUPON', 'COUPON', '" . $value['title'] . "', 1, " . number_format($value['value'] * -1, 2) . ")";
                        xtc_db_query($sql);
                    }
                }

                $total_amount = $_SESSION['pi_ratepay_rate_total_amount'];
                $amount = $_SESSION['pi_ratepay_rate_amount'];
                $interest_amount = $_SESSION['pi_ratepay_rate_interest_amount'];
                $service_charge = $_SESSION['pi_ratepay_rate_service_charge'];
                $annual_percentage_rate = $_SESSION['pi_ratepay_rate_annual_percentage_rate'];
                $monthly_debit_interest = $_SESSION['pi_ratepay_rate_monthly_debit_interest'];
                $number_of_rates = $_SESSION['pi_ratepay_rate_number_of_rates'];
                $rate = $_SESSION['pi_ratepay_rate_rate'];
                $last_rate = $_SESSION['pi_ratepay_rate_last_rate'];
                xtc_db_query("DELETE FROM `pi_ratepay_rate_details` where orderid = '" . xtc_db_input($id) . "'");
                xtc_db_query("INSERT INTO `pi_ratepay_rate_details` (`orderid`,`totalamount`, `amount`, `interestamount`, `servicecharge`, `annualpercentagerate`, `monthlydebitinterest`, `numberofrates`, `rate`, `lastrate`) VALUES ('" . xtc_db_input($id) . "','" . xtc_db_input($total_amount) . "', '" . xtc_db_input($amount) . "', '" . xtc_db_input($interest_amount) . "', '" . xtc_db_input($service_charge) . "', '" . xtc_db_input($annual_percentage_rate) . "', '" . xtc_db_input($monthly_debit_interest) . "', '" . xtc_db_input($number_of_rates) . "','" . xtc_db_input($rate) . "', '" . xtc_db_input($last_rate) . "')");
            } else {
                $_SESSION['disable'] = true;
                $errorStr = urlencode(PI_RATEPAY_RATE_ERROR);
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $errorStr, 'SSL'));
            }
        } else {
            $_SESSION['disable'] = true;
            $errorStr = urlencode(PI_RATEPAY_RATE_ERROR);
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . $errorStr, 'SSL'));
            $this->piRatepayLog($orderId, $transactionId, 'PAYMENT_CONFIRM', 'n/a', $request, false, $first_name, $last_name);
        }

        if ($this->order_status) {
            xtc_db_query("UPDATE " . TABLE_ORDERS . " SET orders_status='" . xtc_db_input($this->order_status) . "' WHERE orders_id='" . xtc_db_input($insert_id) . "'");
        }
    }

    /*
     * Getting the Error
     *
     * @return boolean
     */

    function get_error() {
        return false;
    }

    /*
     * Checks if RatePAY Rate is enabled.
     *
     * @return boolean
     */

    function check() {
        if (!isset($this->_check)) {
            $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PI_RATEPAY_RATE_STATUS'");
            $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }

    /**
     * This method create's all necessary Database entries for RatePAY Rate
     */
    function install() {
        $check_query = xtc_db_query("SHOW TABLES LIKE 'pi_ratepay_rate_orders'");
        if (xtc_db_num_rows($check_query) == 0) {
            xtc_db_query(
                    "CREATE TABLE `pi_ratepay_rate_orders`(
						`id` int(11) NOT NULL auto_increment,
						`order_number` varchar(32) character set latin1 collate latin1_general_ci NOT NULL,
						`transaction_id` varchar(64) NOT NULL,
						`transaction_short_id` varchar(20) NOT NULL,
						`return_amount` decimal(9,2) NOT NULL DEFAULT '0.00',
						`descriptor` varchar(20),
						PRIMARY KEY  (`id`)
						) ENGINE=MyISAM AUTO_INCREMENT=1;"
            );
        }
        $check_query = xtc_db_query("SHOW TABLES LIKE 'pi_ratepay_rate_orderdetails'");
        if (xtc_db_num_rows($check_query) == 0) {
            xtc_db_query(
                    "CREATE TABLE `pi_ratepay_rate_orderdetails` (
						  `id` INT NOT NULL AUTO_INCREMENT,
						  `order_number` VARCHAR( 255 ) NOT NULL ,
						  `article_number` VARCHAR( 255 ) NOT NULL ,
						  `real_article_number` VARCHAR( 255 ) NOT NULL ,
						  `article_name` VARCHAR(255) NOT NULL,
						  `ordered` INT NOT NULL DEFAULT '1',
						  `shipped` INT NOT NULL DEFAULT '0',
						  `cancelled` INT NOT NULL DEFAULT '0',
						  `article_netUnitPrice` decimal(10,2) NOT NULL DEFAULT '0',
						  `returned` INT NOT NULL DEFAULT '0',
						   PRIMARY KEY  (`id`)
						) ENGINE=MyISAM AUTO_INCREMENT=1;"
            );
        }
        $check_query = xtc_db_query("SHOW TABLES LIKE 'pi_ratepay_rate_history'");
        if (xtc_db_num_rows($check_query) == 0) {
            xtc_db_query(
                    "CREATE TABLE `pi_ratepay_rate_history` (
						  `id` INT NOT NULL AUTO_INCREMENT,
						  `order_number` VARCHAR( 255 ) NOT NULL ,
						  `article_number` VARCHAR( 255 ) NOT NULL ,
						  `quantity` INT NOT NULL,
						  `method` VARCHAR( 40 ) NOT NULL,
						  `submethod` VARCHAR( 40 ) NOT NULL DEFAULT '',
						  `date` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
						   PRIMARY KEY  (`id`)
						) ENGINE=MyISAM AUTO_INCREMENT=1;"
            );
        }
        $check_query = xtc_db_query("SHOW TABLES LIKE 'pi_ratepay_log'");
        if (xtc_db_num_rows($check_query) == 0) {
            xtc_db_query(
                    "CREATE TABLE `pi_ratepay_log` (
						  `id` INT NOT NULL AUTO_INCREMENT,
						  `order_number` VARCHAR( 255 ) NOT NULL,
						  `transaction_id` VARCHAR( 255 ) NOT NULL,
						  `payment_method` VARCHAR( 40 ) NOT NULL,
						  `payment_type` VARCHAR( 40 ) NOT NULL,
						  `payment_subtype` VARCHAR( 40 ) NOT NULL,
						  `result` VARCHAR( 40 ) NOT NULL,
						  `request` MEDIUMTEXT NOT NULL,
						  `response` MEDIUMTEXT NOT NULL,
						  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  `result_code` VARCHAR( 10 ) NOT NULL,
                                                  `first_name` VARCHAR( 40 ) NOT NULL DEFAULT '',
                                                  `last_name` VARCHAR( 40 ) NOT NULL DEFAULT '',
                                                  `reason` VARCHAR( 255 ) NOT NULL DEFAULT '',
						   PRIMARY KEY  (`id`)
						) ENGINE=MyISAM AUTO_INCREMENT=1;"
            );
        }

        $check_query = xtc_db_query("SHOW TABLES LIKE 'pi_ratepay_rate_details'");
        if (xtc_db_num_rows($check_query) == 0) {
            xtc_db_query(
                    "CREATE TABLE `pi_ratepay_rate_details` (
						`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
						`orderid` VARCHAR(255) NOT NULL ,
						`totalamount` DOUBLE NOT NULL ,
						`amount` DOUBLE NOT NULL ,
						`interestamount` DOUBLE NOT NULL ,
						`servicecharge` DOUBLE NOT NULL ,
						`annualpercentagerate` DOUBLE NOT NULL ,
						`monthlydebitinterest` DOUBLE NOT NULL ,
						`numberofrates` DOUBLE NOT NULL ,
						`rate` DOUBLE NOT NULL ,
						`lastrate` DOUBLE NOT NULL,
						`checkouttype` VARCHAR(255) DEFAULT '',
						`owner` VARCHAR(255) DEFAULT '',
						`bankaccountnumber` VARCHAR(255) DEFAULT '',
						`bankcode` VARCHAR(255) DEFAULT '',
						`bankname` VARCHAR(255) DEFAULT '',
						`iban` VARCHAR(255) DEFAULT '',
						`bicswift` VARCHAR(255) DEFAULT ''
						) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;"
            );
        }

        $check_query = xtc_db_query("show columns from admin_access like 'pi_ratepay%'");
        if (xtc_db_num_rows($check_query) == 0) {
            xtc_db_query("ALTER TABLE admin_access ADD pi_ratepay_admin INT(1) NOT NULL DEFAULT '0'");
            xtc_db_query("ALTER TABLE admin_access ADD pi_rp_logging INT(1) NOT NULL DEFAULT '0'");
            xtc_db_query("ALTER TABLE admin_access ADD pi_ratepay_order_controller INT(1) NOT NULL DEFAULT '0'");
            xtc_db_query("ALTER TABLE admin_access ADD pi_ratepay_rechnung_print_order INT(1) NOT NULL DEFAULT '0'");
            xtc_db_query("ALTER TABLE admin_access ADD pi_ratepay_rate_print_order INT(1) NOT NULL DEFAULT '0'");
            xtc_db_query("ALTER TABLE admin_access ADD pi_ratepay_admin_xtc_modified INT(1) NOT NULL DEFAULT '0'");
            xtc_db_query("ALTER TABLE admin_access ADD pi_rp_logging_xtc_modified INT(1) NOT NULL DEFAULT '0'");


            xtc_db_query("UPDATE admin_access SET pi_ratepay_admin = '1' WHERE customers_id= '1' OR customers_id= 'groups'");
            xtc_db_query("UPDATE admin_access SET pi_rp_logging = '1' WHERE customers_id= '1' OR customers_id= 'groups'");
            xtc_db_query("UPDATE admin_access SET pi_ratepay_order_controller = '1' WHERE customers_id='1' OR customers_id= 'groups'");
            xtc_db_query("UPDATE admin_access SET pi_ratepay_rechnung_print_order = '1' WHERE customers_id='1' OR customers_id= 'groups'");
            xtc_db_query("UPDATE admin_access SET pi_ratepay_rate_print_order = '1' WHERE customers_id='1' OR customers_id= 'groups'");
            xtc_db_query("UPDATE admin_access SET pi_ratepay_admin_xtc_modified = '1' WHERE customers_id='1' OR customers_id= 'groups'");
            xtc_db_query("UPDATE admin_access SET pi_rp_logging_xtc_modified = '1' WHERE customers_id='1' OR customers_id= 'groups'");
        }

        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_STATUS', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_PROFILE_ID', '', '6', '3', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_SECURITY_CODE', '', '6', '3', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_SANDBOX', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_LOGS', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_GTC', '', '6', '3', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_PRIVACY', '', '6', '3', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_MERCHANT_PRIVACY', '', '6', '3', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_MERCHANT_NAME', '', '6', '3', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_BANK_NAME', '', '6', '3', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_SORT_CODE', '', '6', '3', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_ACCOUNT_NR', '', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_SWIFT', '', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_IBAN', '', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_MAX', '2000', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_MIN', '200', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_EXTRA_FIELD', 'Bei Fragen zur Rechnung wenden Sie sich bitte an <br/>Tel 012/34567 &#9679; Fax 012/345678 &#9679; testshop@ratepay.de', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_SORT_ORDER', '0', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_ORDER_STATUS_ID', '0', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_ALLOWED', '', '6', '0', now())");

        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_OWNER', '', '6', '3', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_HR', '', '6', '3', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_FON', '', '6', '3', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_FAX', '', '6', '3', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_PLZ', '', '6', '3', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_STREET', '', '6', '3', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_COURT', '', '6', '3', now())");
    }

    /*
     * Removes all RatePAY Rate DB Entries
     */

    function remove() {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key in ('" . implode("', '", $this->keys()) . "')");
        xtc_db_query("DROP TABLE `pi_ratepay_rate_history`, `pi_ratepay_rate_orderdetails`, `pi_ratepay_rate_orders`,`pi_ratepay_rate_details`");
    }

    /*
     * Setting all the RatePAY Rate Keys for Configuration
     *
     * @return array
     */

    function keys() {
        return array(
            'MODULE_PAYMENT_PI_RATEPAY_RATE_STATUS',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_PROFILE_ID',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_SECURITY_CODE',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_MIN',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_MAX',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_SANDBOX',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_LOGS',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_GTC',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_PRIVACY',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_MERCHANT_PRIVACY',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_OWNER',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_FON',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_FAX',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_STREET',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_PLZ',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_COURT',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_SHOP_HR',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_MERCHANT_NAME',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_BANK_NAME',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_SORT_CODE',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_ACCOUNT_NR',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_SWIFT',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_IBAN',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_EXTRA_FIELD',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_SORT_ORDER',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_ALLOWED',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_ZONE',
            'MODULE_PAYMENT_PI_RATEPAY_RATE_ORDER_STATUS_ID'
        );
    }

    /**
     * This method send's the PAYMENT_INIT request to the RatePAY API
     * @return SimpleXML
     */
    function paymentInit($order) {
        include('ratepay_webservice/Ratepay_XML.php');

        $systemId = $_SERVER['SERVER_ADDR'];
        $operation = 'PAYMENT_INIT';
        $payment_subtype = 'n/a';

        //PAYMENT_INIT
        $ratepay = new Ratepay_XML;
        $ratepay->live = $this->testOrLive();

        $request = $ratepay->getXMLObject();

        $head = $request->addChild('head');
        $head->addChild('system-id', $systemId);
        $head->addChild('operation', $operation);

        $credential = $head->addChild('credential');
        $credential->addChild('profile-id', $this->profileId);
        $credential->addChild('securitycode', $this->securityCode);

        $response = $ratepay->paymentOperation($request);

        $transactionId = (string) $response->head->{'transaction-id'};
        $transactionShortId = (string) $response->head->{'transaction-short-id'};

        $_SESSION['pi']['tid'] = $transactionId;
        $_SESSION['pi']['tsid'] = $transactionShortId;

        $orderId = 'n/a';

        $first_name = $order->delivery['firstname'];
        $last_name = $order->delivery['lastname'];
        if ($response) {
            $this->piRatepayLog($orderId, $transactionId, $operation, $payment_subtype, $request, $response, $first_name, $last_name);
        } else {
            $this->piRatepayLog($orderId, $transactionId, $operation, $payment_subtype, $request, false, $first_name, $last_name);
        }
        return $response;
    }

    /**
     * This method send's the PAYMENT_REQUEST request to the RatePAY API
     * @return array
     */
    function paymentRequest($order, $xtPrice) {
        include('ratepay_webservice/Ratepay_XML.php');

        $systemId = $_SERVER['SERVER_ADDR'];
        $operation = 'PAYMENT_REQUEST';
        $payment_subtype = 'n/a';
        $tid = $_SESSION['pi']['tid'];
        $tsid = $_SESSION['pi']['tsid'];
        $customerId = $_SESSION ['customer_id'];
        $currency = $_SESSION ['currency'];

        $query = xtc_db_query("SELECT customers_gender, DATE_FORMAT(customers_dob, '%Y-%m-%d') as customers_dob, customers_email_address, customers_telephone, customers_fax, customers_vat_id from " . TABLE_CUSTOMERS . " WHERE customers_id ='" . xtc_db_input($customerId) . "' ");
        $customerXTC = xtc_db_fetch_array($query);

        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
            $total = $order->info['total'] + $order->info['tax'];
        } else {
            $total = $order->info['total'];
        }

        if (empty($_SESSION['pi']['coupon']) == false) {
            foreach ($_SESSION['pi']['coupon'] as $value) {
                $total = $total - $value['value'];
            }
        }

        $shippingTaxAmount = number_format($this->getShippingTaxAmount($order), 2);
        if ($shippingTaxAmount > 0) {
            $total = $total + $shippingTaxAmount;
        }

        $ratepay = new Ratepay_XML;
        $ratepay->live = $this->testOrLive();

        $request = $ratepay->getXMLObject();

        $head = $request->addChild('head');
        $head->addChild('system-id', $systemId);
        $head->addChild('transaction-id', $tid);
        $head->addChild('transaction-short-id', $tsid);
        $head->addChild('operation', $operation);

        $credential = $head->addChild('credential');
        $credential->addChild('profile-id', $this->profileId);
        $credential->addChild('securitycode', $this->securityCode);

        $customerDevice = $head->addChild('customer-device');

        $httpHeaderList = $customerDevice->addChild('http-header-list');

        $header = $httpHeaderList->addChild('header', 'text/xml');
        $header->addAttribute('name', 'Accept');
        $header = $httpHeaderList->addChild('header', 'utf-8');
        $header->addAttribute('name', 'Accept-Charset');
        $header = $httpHeaderList->addChild('header', 'x86');
        $header->addAttribute('name', 'UA-CPU');

        $request->addChild('content');
        $content = $request->content;
        $content->addChild('customer');

        $customer = $content->customer;

        if (strtoupper($customerXTC['customers_gender']) == "F") {
            $gender = "F";
        } else if (strtoupper($customerXTC['customers_gender']) == "M") {
            $gender = "M";
        } else {
            $gender = "U";
        }
        $customer->addCDataChild('first-name', $this->removeSpecialChars($order->delivery['firstname']));
        $customer->addCDataChild('last-name', $this->removeSpecialChars($order->delivery['lastname']));
        $customer->addChild('gender', $gender);
        $customer->addChild('date-of-birth', $customerXTC['customers_dob']);
        $customer->addChild('ip-address', $this->getRatepayCustomerIpAddress());
        if ($customerXTC['customers_vat_id'] != '' && $order->customer['company'] != '') {
            $customer->addCDataChild('company-name', $this->removeSpecialChars($order->customer['company']));
            $customer->addChild('vat-id', $customerXTC['customers_vat_id']);
        }

        $customer->addChild('contacts');
        $contacts = $customer->contacts;
        $contacts->addChild('email', utf8_encode($customerXTC['customers_email_address']));
        $contacts->addChild('phone');

        $phone = $contacts->phone;
        $phone->addChild('direct-dial', $customerXTC['customers_telephone']);

        if ($customerXTC['customers_fax'] != "") {
            $contacts->addChild('fax');
            $fax = $contacts->fax;
            $fax->addChild('direct-dial', $customerXTC['customers_fax']);
        }

        $customer->addChild('addresses');
        $addresses = $customer->addresses;
        $addresses->addChild('address');
        $addresses->addChild('address');

        $billingAddress = $addresses->address[0];
        $shippingAddress = $addresses->address[1];

        $billingAddress->addAttribute('type', 'BILLING');
        $shippingAddress->addAttribute('type', 'DELIVERY');

        $billingAddress->addCDataChild('street', $this->removeSpecialChars($order->delivery['street_address']));
        $billingAddress->addChild('zip-code', $order->delivery['postcode']);
        $billingAddress->addCDataChild('city', $this->removeSpecialChars($order->delivery['city']));
        $billingAddress->addChild('country-code', $order->delivery['country']['iso_code_2']);

        $shippingAddress->addCDataChild('street', $this->removeSpecialChars($order->delivery['street_address']));
        $shippingAddress->addChild('zip-code', $order->delivery['postcode']);
        $shippingAddress->addCDataChild('city', $this->removeSpecialChars($order->delivery['city']));
        $shippingAddress->addChild('country-code', $order->delivery['country']['iso_code_2']);

        $customer->addChild('nationality', $order->delivery['country']['iso_code_2']);
        $customer->addChild('customer-allow-credit-inquiry', 'yes');

        if ($_SESSION['pi']['company'] != '') {

            $customer->addChild('vat-id', $_SESSION['pi']['vatid']);
        }

        $content->addChild('shopping-basket');
        $shoppingBasket = $content->{'shopping-basket'};
        $shoppingBasket->addAttribute('amount', number_format(round($total, $xtPrice->get_decimal_places($currency)), 2, ".", ""));
        $shoppingBasket->addAttribute('currency', 'EUR');

        $shoppingBasket->addChild('items');

        $items = $shoppingBasket->items;
        for ($i = 0; $i < sizeof($order->products); $i++) {

            $price = round($order->products[$i]['price'], $xtPrice->get_decimal_places($currency));
            $qty = intval($order->products[$i]['qty']);
            if ($price > 0) {
                $items->addCDataChild('item', $this->removeSpecialChars($order->products[$i]['name']));
                $items->item[$i]->addAttribute('article-number', $order->products[$i]['id']);
                $items->item[$i]->addAttribute('quantity', $qty);
                $items->item[$i]->addAttribute('unit-price', number_format($price / (100 + floatval($order->products[$i]['tax'])) * 100, 2, ".", ""));
                $items->item[$i]->addAttribute('total-price', number_format(($price / (100 + floatval($order->products[$i]['tax'])) * 100) * $qty, 2, ".", ""));
                $items->item[$i]->addAttribute('tax', number_format($qty * ($price / (100 + floatval($order->products[$i]['tax'])) * floatval($order->products[$i]['tax'])), 2, ".", ""));
            }
        }

        $shippingCost = number_format($order->info['shipping_cost'], 2);

        if ($shippingCost > 0) {
            $_SESSION['pi_ratepay']['shipping'] = $shippingCost;
            $items->addChild('item', 'Versand');
            $items->item[$i]->addAttribute('article-number', 'SHIPPING');
            $items->item[$i]->addAttribute('quantity', '1');
            $items->item[$i]->addAttribute('unit-price', number_format($shippingCost, 2, ".", ""));
            $items->item[$i]->addAttribute('total-price', number_format($shippingCost, 2, ".", ""));
            $items->item[$i]->addAttribute('tax', $shippingTaxAmount);
        }
        $discount_price = $xtPrice->xtcFormat($order->info['subtotal'], false) / 100 * $_SESSION['customers_status']['customers_status_ot_discount'];
        if (isset($_SESSION['customers_status']['customers_status_ot_discount'])) {
            if ($discount_price > 0) {
                $_SESSION['pi_ratepay']['discount'] = $discount_price;
                $discount_price = $discount_price * -1;
                $i++;
                $items->addChild('item', 'Rabatt');
                $items->item[$i]->addAttribute('article-number', 'DISCOUNT');
                $items->item[$i]->addAttribute('quantity', '1');
                $items->item[$i]->addAttribute('unit-price', number_format($discount_price, 2, ".", ""));
                $items->item[$i]->addAttribute('total-price', number_format($discount_price, 2, ".", ""));
                $items->item[$i]->addAttribute('tax', '0.00');
            }
        }

        if (empty($_SESSION['pi']['coupon']) == false) {
            foreach ($_SESSION['pi']['coupon'] as $value) {
                $items->addChild('item', $value['title']);
                $i++;
                $items->item[$i]->addAttribute('article-number', 'COUPON');
                $items->item[$i]->addAttribute('quantity', '1');
                $items->item[$i]->addAttribute('unit-price', number_format($this->getCouponAmount($value['value']), 2, ".", ""));
                $items->item[$i]->addAttribute('total-price', number_format($this->getCouponAmount($value['value']), 2, ".", ""));
                $items->item[$i]->addAttribute('tax', number_format($this->getCouponTaxAmount($value['value']), 2, ".", ""));
            }
        }
        $content->addChild('payment');
        $payment = $content->payment;
        $payment->addAttribute('method', 'INSTALLMENT');
        $payment->addAttribute('currency', 'EUR');
        $payment->addChild('amount', number_format($_SESSION['pi_ratepay_rate_total_amount'], 2, ".", ""));
        $installment = $payment->addChild('installment-details');
        $installment->addChild('installment-number', $_SESSION['pi_ratepay_rate_number_of_rates']);
        $installment->addChild('installment-amount', $_SESSION['pi_ratepay_rate_rate']);
        $installment->addChild('last-installment-amount', $_SESSION['pi_ratepay_rate_last_rate']);
        $installment->addChild('interest-rate', $_SESSION['pi_ratepay_rate_interest_rate']);
        $payment->addChild('debit-pay-type', 'BANK-TRANSFER');

        $response = $ratepay->paymentOperation($request);
        $return = array($request, $response);

        return $return;
    }

    /**
     * This method send's the PAYMENT_CONFIRM request to the RatePAY API
     * @return array $return
     */
    function paymentConfirm($orderId) {
        $ratepay = new Ratepay_XML;
        $ratepay->live = $this->testOrLive();

        $request = $ratepay->getXMLObject();
        $tid = $_SESSION['pi']['tid'];
        $tsid = $_SESSION['pi']['tsid'];
        $operation = 'PAYMENT_CONFIRM';
        $systemId = $_SERVER['SERVER_ADDR'];
        $head = $request->addChild('head');
        $head->addChild('system-id', $systemId);
        $head->addChild('transaction-id', $tid);
        $head->addChild('transaction-short-id', $tsid);
        $head->addChild('operation', $operation);

        $credential = $head->addChild('credential');
        $credential->addChild('profile-id', $this->profileId);
        $credential->addChild('securitycode', $this->securityCode);

        $external = $head->addChild('external');

        $external->addChild('order-id', $orderId);

        $response = $ratepay->paymentOperation($request);
        $return = array($request, $response);

        return $return;
    }

    /**
     * This method save's all necessary request and response informations in the database
     * @param string $orderId
     * @param string $transactionId
     * @param string $payment_type
     * @param string $payment_subtype
     * @param string $request
     * @param string $response
     * @param string $first_name
     * @param string $last_name
     */
    function piRatepayLog($orderId, $transactionId, $payment_type, $payment_subtype, $request, $response = false, $first_name = '', $last_name = '') {
        $logging = $this->logs;
        if ($logging == true) {
            $responseXML = '';
            $reasonText = '';
            $result = '';
            $resultCode = '';
            if ($response) {
                $responseXML = $response->asXML();
                $result = (string) $response->head->processing->result;
                $resultCode = (string) $response->head->processing->result->attributes()->code;
                $reasonText = (string) $response->head->processing->reason;
            } else {
                $result = "Service unavaible.";
                $resultCode = "Service unavaible.";
            }

            $requestXML = $request->asXML();

            $sql = "INSERT INTO pi_ratepay_log (order_number, transaction_id, payment_method, payment_type,  payment_subtype, result, request, response, result_code, first_name, last_name, reason) VALUES ('" . xtc_db_input($orderId) . "', '" . xtc_db_input($transactionId) . "', 'INSTALLMENT','" . xtc_db_input($payment_type) . "', '" . xtc_db_input($payment_subtype) . "', '" . xtc_db_input($result) . "','" . xtc_db_input($requestXML) . "','" . xtc_db_input($responseXML) . "','" . xtc_db_input($resultCode) . "','" . xtc_db_input($first_name) . "','" . xtc_db_input($last_name) . "','" . xtc_db_input($reasonText) . "')";

            xtc_db_query($sql);

            if ($payment_type == "PAYMENT_CONFIRM") {
                $sql = "UPDATE pi_ratepay_log set order_number = '" . xtc_db_input($orderId) . "' where transaction_id = '" . xtc_db_input($transactionId) . "';";
                xtc_db_query($sql);
            }
        }
    }

    /**
     * This method check's if it's live or test
     *
     * @return string
     */
    function testOrLiveUsage() {
        if (($this->sandbox == false)) {
            $usage = 'Produktionskauf';
        } else {
            $usage = 'Testeinkauf';
        }
        return $usage;
    }

    /**
     * This method check's if it's live or test
     *
     * @return boolean
     */
    function testOrLive() {
        if (($this->sandbox == false)) {
            $usage = true;
        } else {
            $usage = false;
        }
        return $usage;
    }

    /*
     * This method returns the IP of the Customer
     *
     * @return string
     */

    function getRatepayCustomerIpAddress() {
        $systemId = "";
        if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
            $systemId = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $systemId = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $systemId = $_SERVER['REMOTE_ADDR'];
        }
        return $systemId;
    }

    /*
     * This method removes some special chars
     *
     * @return string
     */

    private function removeSpecialChars($str) {
        $search = array("–", "´", "‹", "›", "‘", "’", "‚", "“", "”", "„", "‟", "•", "‒", "―", "—", "™", "¼", "½", "¾");
        $replace = array("-", "'", "<", ">", "'", "'", ",", '"', '"', '"', '"', "-", "-", "-", "-", "TM", "1/4", "1/2", "3/4");
        return $this->removeSpecialChar($search, $replace, $str);
    }

    /*
     * This method removes some special chars
     *
     * @return string
     */

    private function removeSpecialChar($search, $replace, $subject) {
        $str = str_replace($search, $replace, $subject);
        return utf8_encode($str);
    }

    /**
     * Add the shipping tax to the order object
     * 
     * @param order $order
     * @return float
     */
    function getShippingTaxAmount($order) {
        $taxPercent = $this->getShippingTaxRate($order);
        $shippingTaxAmount = $order->info['shipping_cost'] * ($taxPercent / 100);

        return $shippingTaxAmount;
    }

    /**
     * Retrieve the shipping tax rate
     * 
     * @param order $order
     * @return float 
     */
    function getShippingTaxRate($order) {
        $shipping_class_array = explode("_", $order->info['shipping_class']);
        $shipping_class = strtoupper($shipping_class_array[0]);
        if (empty($shipping_class)) {
            $shipping_tax_rate = 0;
        } else {
            $const = 'MODULE_SHIPPING_' . $shipping_class . '_TAX_CLASS';

            if (defined($const)) {
                $shipping_tax_rate = xtc_get_tax_rate(constant($const));
            } else {
                $shipping_tax_rate = 0;
            }
        }

        return $shipping_tax_rate;
    }

    /**
     * Retrive the coupon tax rate 
     * 
     * @return float  
     */
    function getCouponTaxRate() {
        if ($this->piCouponIncTax == 'true') {
            $const = 'MODULE_ORDER_TOTAL_COUPON_TAX_CLASS';
            if (defined($const)) {
                $couponTaxClass = xtc_get_tax_rate(constant($const));
            } else {
                $couponTaxClass = 0;
            }
        } else {
            $couponTaxClass = 0;
        }
        return $couponTaxClass;
    }

    /**
     * Retrive the coupon tax amount 
     * 
     * @param float $amount
     * @return float  
     */
    function getCouponTaxAmount($amount) {
        $taxAmount = (($amount / (100 + $this->getCouponTaxRate()) * 100) - $amount);

        return $taxAmount;
    }

    /**
     * Retrieve the coupon amount 
     * 
     * @param float $amount
     * @return float 
     */
    function getCouponAmount($amount) {
        $amount = $amount * (-1);
        return $amount + $this->getCouponTaxAmount($amount);
    }

}

?>