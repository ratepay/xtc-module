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
 * @package   PayIntelligent_RatePAY
 * @copyright (C) 2010 PayIntelligent GmbH  <http://www.payintelligent.de/>
 * @license   GPLv2
 */
//Ratepay Admin

//Tabellen überschriften
define('RATEPAY_ORDER_RATEPAY_NAME', 'RatePAY Rechnung');
define('RATEPAY_ORDER_RATEPAY_ADMIN_DELIVER_CANCEL', 'Lieferung / Stornierung');
define('RATEPAY_ORDER_RATEPAY_ADMIN_RETOUR', 'Retoure');
define('RATEPAY_ORDER_RATEPAY_ADMIN_RETOURE_BUTTON', 'retournieren');
define('RATEPAY_ORDER_RATEPAY_ADMIN_HISTORY', 'Historie');
define('RATEPAY_ORDER_RATEPAY_ADMIN_GOODWILL', 'Gutschrift');
define('RATEPAY_ORDER_RATEPAY_ADMIN_RATE_DETAILS', 'Raten Details');
// end tabellen überschriften

//buttons
define('RATEPAY_ORDER_RATEPAY_ADMIN_DELIVERY', 'versenden');
define('RATEPAY_ORDER_RATEPAY_ADMIN_CANCELLATION', 'stornieren');
define('RATEPAY_ORDER_RATEPAY_ADMIN_CREATE_GOODWILL', 'Gutschrift erzeugen');
//end buttons

//table heads
define('RATEPAY_ORDER_RATEPAY_ADMIN_QTY', 'Anzahl');
define('RATEPAY_ORDER_RATEPAY_ART_ID', 'Art.-Nr.');
define('RATEPAY_ORDER_RATEPAY_ADMIN_PRODUCT_NAME', 'Bezeichnung');
define('RATEPAY_ORDER_RATEPAY_ADMIN_PRICE_NETTO', 'Preis (Netto)');
define('RATEPAY_ORDER_RATEPAY_ADMIN_TAX_AMOUNT', 'Prozentsatz Steuern');
define('RATEPAY_ORDER_RATEPAY_ADMIN_ROW_PRICE', 'Gesamtpreis (Brutto)');
define('RATEPAY_ORDER_RATEPAY_ADMIN_ORDERED', 'Bestellt');
define('RATEPAY_ORDER_RATEPAY_ADMIN_DELIVERED', 'Geliefert');
define('RATEPAY_ORDER_RATEPAY_ADMIN_CANCELED', 'Storniert');
define('RATEPAY_ORDER_RATEPAY_ADMIN_RETURNED', 'Retourniert');
define('RATEPAY_ORDER_RATEPAY_ADMIN_GOODWILL_AMOUNT', 'Wert');
define('RATEPAY_ORDER_RATEPAY_ADMIN_ACTION', 'Action');
define('RATEPAY_ORDER_RATEPAY_ADMIN_DATE', 'Datum');
//end table heads

//end Ratepay admin

//Ratepay logging
// Tabellen �berschrift
define('RATEPAY_ADMIN_LOGGING', 'Logging');
// end tabellen �berschriften

// table heads
define('RATEPAY_ADMIN_LOGGING_ID', 'ID');
define('RATEPAY_ADMIN_LOGGING_ORDER_ID', 'ORDER ID');
define('RATEPAY_ADMIN_LOGGING_TRANSACTION_ID', 'TRANSACTION ID');
define('RATEPAY_ADMIN_LOGGING_PAYMENT_METHOD', 'PAYMENT METHOD');
define('RATEPAY_ADMIN_LOGGING_OPERATION_TYPE', 'OPERATION TYPE');
define('RATEPAY_ADMIN_LOGGING_OPERATION_SUBTYPE', 'OPERATION SUBTYPE');
define('RATEPAY_ADMIN_LOGGING_RESULT', 'RESULT');
define('RATEPAY_ADMIN_LOGGING_RATEPAY_RESULT', 'RATEPAY RESULT');
define('RATEPAY_ADMIN_LOGGING_RATEPAY_RESULT_CODE', 'RATEPAY RESULT CODE');
define('RATEPAY_ADMIN_LOGGING_REQUEST', 'REQUEST');
define('RATEPAY_ADMIN_LOGGING_RESPONSE', 'RESPONSE');
define('RATEPAY_ADMIN_LOGGING_DATE', 'DATE');
//end table heads

//delete text
define('RATEPAY_ADMIN_LOGGING_DELETE_TEXT_1', 'Alle Eintr&auml;ge die &auml;lter als');
define('RATEPAY_ADMIN_LOGGING_DELETE_TEXT_2', 'Tage sind ');
//end delete text
//buttons
define('RATEPAY_ADMIN_LOGGING_DELETE', 'L&ouml;schen');
define('RATEPAY_ADMIN_LOGGING_DELETE_SUCCESS', 'L&ouml;schen war erfolgreich.');
//end buttons
//end Ratepay logging

//RatePAY Order Overview
define('PI_RATEPAY_SUCCESSPARTIALCANCELLATION','Teilstornierung war erfolgreich.');
define('PI_RATEPAY_SUCCESSFULLCANCELLATION','Komplettstornierung war erfolgreich.');
define('PI_RATEPAY_SUCCESSPARTIALRETURN','Teilretournierung war erfolgreich.');
define('PI_RATEPAY_SUCCESSFULLRETURN','Komplettretournierung war erfolgreich.');
define('PI_RATEPAY_SUCCESSDELIVERY','Lieferung war erfolgreich.');
define('PI_RATEPAY_SUCCESSVOUCHER','Gutschrift wurde erfolgreich ausgef&uuml;hrt');
define('PI_RATEPAY_ERRORPARTIALCANCELLATION','Teilstornierung war nicht erfolgreich.');
define('PI_RATEPAY_ERRORFULLCANCELLATION','Komplettstornierung war nicht erfolgreich.');
define('PI_RATEPAY_ERRORPARTIALRETURN','Teilretournierung war nicht erfolgreich.');
define('PI_RATEPAY_ERRORFULLRETURN','Komplettretournierung war nicht erfolgreich.');
define('PI_RATEPAY_ERRORDELIVERY','Lieferung war nicht erfolgreich.');
define('PI_RATEPAY_ERRORVOUCHER','Gutschrift wurde nicht erfolgreich ausgef&uuml;hrt');
define('PI_RATEPAY_ERRORTYPING','Falsche Eingabe. Eingabe wurde zur&uuml;ckgesetzt. Sie d&uuml;rfen nur Zahlen eintragen, die den vorausgef&uuml;llten Wert nicht &uuml;berschreiten.');
define('PI_RATEPAY_SERVICE','Service offline!');

define('PI_RATEPAY_SHIPPED','Geliefert');
define('PI_RATEPAY_RETURNED','Retourniert');
define('PI_RATEPAY_CANCELLED','Storniert');
define('PI_RATEPAY_CREDIT','Gutschrift');

define('PI_RATEPAY_VOUCHER', 'Anbieter Gutschrift');

?>