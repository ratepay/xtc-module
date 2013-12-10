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

require_once('includes/application_top.php');
// include needed functions
require_once(DIR_FS_INC . 'xtc_get_order_data.inc.php');
require_once(DIR_FS_INC . 'xtc_get_attributes_model.inc.php');
require_once(DIR_FS_INC . 'xtc_not_null.inc.php');
require_once(DIR_FS_INC . 'xtc_format_price_order.inc.php');
require_once ('../includes/modules/payment/ratepay_rechnung.php');
require_once ('../includes/classes/ratepay/helpers/Data.php');
require_once ('../includes/classes/ratepay/helpers/Db.php');
require_once ('../includes/classes/ratepay/helpers/Globals.php');
require_once(DIR_WS_CLASSES . 'order.php');
$smarty = new Smarty;
$order_query_check = xtc_db_query("SELECT customers_id FROM " . TABLE_ORDERS . " WHERE orders_id='" . (int) $_GET['oID'] . "'");
$order_check = xtc_db_fetch_array($order_query_check);
// get order data
$order = new order($_GET['oID']);


$smarty->assign('address_label_customer', xtc_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />'));
$smarty->assign('address_label_shipping', xtc_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'));
$smarty->assign('address_label_payment', xtc_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />'));
$smarty->assign('csID', $order->customer['csID']);
// get products data
$order_query = xtc_db_query("SELECT "
        . "products_id, "
        . "orders_products_id, "
        . "products_model, "
        . "products_name, "
        . "final_price, "
        . "products_quantity "
        . "FROM " . TABLE_ORDERS_PRODUCTS . " "
        . "WHERE orders_id='" . (int) $_GET['oID'] . "'");
$order_data = array();
while ($order_data_values = xtc_db_fetch_array($order_query)) {
    $attributes_query = xtc_db_query("SELECT "
            . "products_options, "
            . "products_options_values, "
            . "price_prefix, "
            . "options_values_price "
            . "FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " "
            . "WHERE orders_products_id='" . $order_data_values['orders_products_id'] . "'");
    $attributes_data = '';
    $attributes_model = '';
    while ($attributes_data_values = xtc_db_fetch_array($attributes_query)) {
        $attributes_data .='<br />' . $attributes_data_values['products_options'] . ':' . $attributes_data_values['products_options_values'];
        $attributes_model .='<br />' . xtc_get_attributes_model($order_data_values['products_id'], $attributes_data_values['products_options_values'], $attributes_data_values['products_options']);
    }

    $order_data[] = array(
        'PRODUCTS_MODEL' => $order_data_values['products_model'],
        'PRODUCTS_NAME' => $order_data_values['products_name'],
        'PRODUCTS_ATTRIBUTES' => $attributes_data,
        'PRODUCTS_ATTRIBUTES_MODEL' => $attributes_model,
        'PRODUCTS_PRICE' => xtc_format_price_order($order_data_values['final_price'], 1, $order->info['currency']),
        'PRODUCTS_QTY' => $order_data_values['products_quantity']);
}
// get order_total data
$oder_total_query = xtc_db_query("SELECT "
        . "title, "
        . "text, "
        . "class, "
        . "value, "
        . "sort_order "
        . "FROM " . TABLE_ORDERS_TOTAL . " "
        . "WHERE orders_id='" . $_GET['oID'] . "' "
        . "ORDER BY sort_order ASC");
$order_total = array();
while ($oder_total_values = xtc_db_fetch_array($oder_total_query)) {

    $order_total[] = array(
        'TITLE' => $oder_total_values['title'],
        'CLASS' => $oder_total_values['class'],
        'VALUE' => $oder_total_values['value'],
        'TEXT' => $oder_total_values['text']);
    if ($oder_total_values['class'] = 'ot_total') {
        $total = $oder_total_values['value'];
    }
}

// assign language to template for caching
$smarty->assign('language', $_SESSION['language']);
$smarty->assign('logo_path', HTTP_SERVER . DIR_WS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/img/');
$smarty->assign('oID', $_GET['oID']);
if ($order->info['payment_method'] != '' && $order->info['payment_method'] != 'no_payment') {
    include(DIR_FS_CATALOG . 'lang/' . $_SESSION['language'] . '/modules/payment/' . $order->info['payment_method'] . '.php');
    $payment_method = constant(strtoupper('MODULE_PAYMENT_' . $order->info['payment_method'] . '_TEXT_TITLE'));
    $smarty->assign('PAYMENT_METHOD', $payment_method);
}
$smarty->assign('COMMENTS', $order->info['comments']);
$smarty->assign('DATE', xtc_date_long($order->info['date_purchased']));
$smarty->assign('order_data', $order_data);
$smarty->assign('order_total', $order_total);

// Get the RatePAY stuff for the Invoice
$piRatepay = Loader::getRatepayPayment($order->info['payment_method']);
$smarty->assign('accountHolder', $piRatepay->shopOwner);
$smarty->assign('bank', $piRatepay->shopBankName);
$smarty->assign('sortCode', $piRatepay->shopSortCode);
$smarty->assign('accountNr', $piRatepay->shopAccountNumber);
$smarty->assign('descriptor', Db::getRatepayOrderDataEntry(Globals::getParam('oID'), 'descriptor'));
$smarty->assign('iban', $piRatepay->shopIban);
$smarty->assign('swift', $piRatepay->shopSwift);
$smarty->assign('email', Db::getShopConfigEntry('STORE_OWNER_EMAIL_ADDRESS'));
$smarty->assign('extraField', $piRatepay->extraInvoiceField);
$smarty->assign('url', $_SERVER['SERVER_NAME']);
$smarty->assign('ust', Db::getShopConfigEntry('STORE_OWNER_VAT_ID'));
$smarty->assign('owner', $piRatepay->shopOwner);
$smarty->assign('address_footer', $piRatepay->shopStreet . ", " . $piRatepay->shopZipCode);
$smarty->assign('fon', $piRatepay->shopPhone);
$smarty->assign('fax', $piRatepay->shopFax);
$smarty->assign('court', $piRatepay->shopCourt);
$smarty->assign('hr', $piRatepay->shopHr);
$smarty->assign('ownerText', RATEPAY_RECHNUNG_PDF_OWNER);
$smarty->assign('fonText', RATEPAY_RECHNUNG_PDF_FON);
$smarty->assign('faxText', RATEPAY_RECHNUNG_PDF_FAX);
$smarty->assign('emailText', RATEPAY_RECHNUNG_PDF_EMAIL);
$smarty->assign('courtText', RATEPAY_RECHNUNG_PDF_COURT);
$smarty->assign('hrText', RATEPAY_RECHNUNG_PDF_HR);
$smarty->assign('ustText', RATEPAY_RECHNUNG_PDF_UST);
$smarty->assign('bullText', RATEPAY_RECHNUNG_PDF_BULL);
$smarty->assign('accountholderText', RATEPAY_RECHNUNG_PDF_ACCOUNTHOLDER);
$smarty->assign('banknameText', RATEPAY_RECHNUNG_PDF_BANKNAME);
$smarty->assign('bankcodenumberText', RATEPAY_RECHNUNG_PDF_BANKCODENUMBER);
$smarty->assign('accountnumberText', RATEPAY_RECHNUNG_PDF_ACCOUNTNUMBER);
$smarty->assign('swiftbicText', RATEPAY_RECHNUNG_PDF_SWIFTBIC);
$smarty->assign('ibanText', RATEPAY_RECHNUNG_PDF_IBAN);
$smarty->assign('intdescText', RATEPAY_RECHNUNG_PDF_INTERNATIONALDESC);
$smarty->assign('paytransferText', RATEPAY_RECHNUNG_PDF_PAYTRANSFER);
$smarty->assign('payuntilText', RATEPAY_RECHNUNG_PDF_PAYUNTIL);
$smarty->assign('referenceText', RATEPAY_RECHNUNG_PDF_REFERENCE);
$smarty->assign('additionalText1', RATEPAY_RECHNUNG_PDF_ADDITIONALINFO_1);
$smarty->assign('additionalText2', RATEPAY_RECHNUNG_PDF_ADDITIONALINFO_2);
$smarty->assign('additionalText3', RATEPAY_RECHNUNG_PDF_ADDITIONALINFO_3);
$smarty->assign('additionalText4', RATEPAY_RECHNUNG_PDF_ADDITIONALINFO_4);
$smarty->assign('abovearticleText', RATEPAY_RECHNUNG_PDF_ABOVEARTICLE);
$smarty->assign('descriptorText', RATEPAY_RECHNUNG_PDF_DESCRIPTOR);
$smarty->assign('selectedPaymentText', RATEPAY_RECHNUNG_PDF_SELECTEDPAYMENT);
// End RatePAY stuff
// 
// dont allow cache
$smarty->caching = false;

$smarty->template_dir = DIR_FS_CATALOG . 'templates';
$smarty->compile_dir = DIR_FS_CATALOG . 'templates_c';
$smarty->config_dir = DIR_FS_CATALOG . 'lang';

$smarty->display(CURRENT_TEMPLATE . '/admin/ratepay_rechnung_print_order.html');
