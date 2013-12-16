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
//XTC STANDARD BEGIN
include ('includes/application_top.php');

// create smarty elements
$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/source/boxes.php');

// include needed functions
require_once (DIR_FS_INC . 'xtc_address_label.inc.php');
require_once (DIR_FS_INC . 'xtc_get_address_format_id.inc.php');
require_once (DIR_FS_INC . 'xtc_check_stock.inc.php');
require_once ('lang/' . $_SESSION["language"] . '/modules/payment/ratepay_rate.php');

unset($_SESSION['tmp_oID']);
// if the customer is not logged on, redirect them to the login page
if (!isset($_SESSION['customer_id'])) {
    if (ACCOUNT_OPTIONS == 'guest') {
        xtc_redirect(xtc_href_link(FILENAME_CREATE_GUEST_ACCOUNT, '', 'SSL'));
    } else {
        xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
    }
}

// if there is nothing in the customers cart, redirect them to the shopping cart page
if ($_SESSION['cart']->count_contents() < 1) {
    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

// if no shipping method has been selected, redirect the customer to the shipping method selection page
if (!isset($_SESSION['shipping'])) {
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}

// avoid hack attempts during the checkout procedure by checking the internal cartID
if (isset($_SESSION['cart']->cartID) && isset($_SESSION['cartID'])) {
    if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
}

if (isset($_SESSION['credit_covers'])) {
    unset($_SESSION['credit_covers']); //ICW ADDED FOR CREDIT CLASS SYSTEM
}

    
// Stock Check
if ((STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true')) {
    $products = $_SESSION['cart']->get_products();
    $any_out_of_stock = 0;
    for ($i = 0, $n = sizeof($products); $i < $n; $i++) {
        if (xtc_check_stock($products[$i]['id'], $products[$i]['quantity']))
            $any_out_of_stock = 1;
    }
    
    if ($any_out_of_stock == 1) {
        xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
    }
}

// if no billing destination address was selected, use the customers own address as default
if (!isset($_SESSION['billto'])) {
    $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
} else {
    // verify the selected billing address
    $check_address_query = xtc_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int) $_SESSION['customer_id'] . "' and address_book_id = '" . (int) $_SESSION['billto'] . "'");
    $check_address = xtc_db_fetch_array($check_address_query);
    if ($check_address['total'] != '1') {
        $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
        if (isset($_SESSION['payment'])) {
            unset($_SESSION['payment']);
        }
    }
}

if (!isset($_SESSION['sendto']) || $_SESSION['sendto'] == "") {
    $_SESSION['sendto'] = $_SESSION['billto'];
}

require (DIR_WS_CLASSES . 'order.php');

$breadcrumb->add('RatePAY Terms', xtc_href_link('ratepay_rechnung_checkout_terms.php', '', 'SSL'));
$breadcrumb->add('RatePAY Ratecalculator', xtc_href_link('ratepay_rate_checkout_details.php', '', 'SSL'));
//XTC STANDARD END
if (!array_key_exists('conditions', $_REQUEST)) {
    xtc_redirect(xtc_href_link('checkout_payment.php', 'error_message=' . urlencode(RATEPAY_RATE_AGB_MISSING), 'SSL'));
}

require (DIR_WS_INCLUDES . 'header.php');

$smarty->assign('backLink', xtc_href_link('ratepay_rate_checkout_terms.php'));
$smarty->assign('forwardLink', xtc_href_link('checkout_confirmation.php'));

$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE . '/module/ratepay_rate_checkout_details.html');

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);


$smarty->caching = 0;

if (!defined(RM))
    $smarty->load_filter('output', 'note');

$smarty->display(CURRENT_TEMPLATE . '/index.html');

include ('includes/application_bottom.php');
?>