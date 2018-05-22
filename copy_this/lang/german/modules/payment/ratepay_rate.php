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

// Config text and description
define('MODULE_PAYMENT_RATEPAY_RATE_TEXT_DESCRIPTION', 'Bieten Sie Ihren Kunden felxiblen Ratenkauf');
define('MODULE_PAYMENT_RATEPAY_RATE_TEXT', 'RatePAY Rate');
define('MODULE_PAYMENT_RATEPAY_RATE_TEXT_TITLE', 'RatePAY Rate');
define('MODULE_PAYMENT_RATEPAY_RATE_TEXT_DESCRIPTION', '');
define('MODULE_PAYMENT_RATEPAY_RATE_STATUS_TITLE', 'Zahlart aktivieren');
define('MODULE_PAYMENT_RATEPAY_RATE_STATUS_DESC', 'Aktivieren Sie RatePAY Rate');
define('MODULE_PAYMENT_RATEPAY_RATE_SANDBOX_TITLE', 'Sandbox');
define('MODULE_PAYMENT_RATEPAY_RATE_SANDBOX_DESC', 'Testserver oder Livebetrieb?');
define('MODULE_PAYMENT_RATEPAY_RATE_PROFILE_ID_DE_TITLE', 'Profil ID DE');
define('MODULE_PAYMENT_RATEPAY_RATE_PROFILE_ID_DE_DESC', 'Ihre von RatePAY zugewiesende ID f&uumlr Deutschland');
define('MODULE_PAYMENT_RATEPAY_RATE_SECURITY_CODE_DE_TITLE', 'Sicherheitsschl&uuml;ssel DE');
define('MODULE_PAYMENT_RATEPAY_RATE_SECURITY_CODE_DE_DESC', 'Ihr von RatePAY zugewiesener Sicherheitsschl&uuml;ssel f&uumlr Deutschland');
define('MODULE_PAYMENT_RATEPAY_RATE_PROFILE_ID_AT_TITLE', 'Profil ID AT');
define('MODULE_PAYMENT_RATEPAY_RATE_PROFILE_ID_AT_DESC', 'Ihre von RatePAY zugewiesende ID f&uumlr &Ouml;sterreich');
define('MODULE_PAYMENT_RATEPAY_RATE_SECURITY_CODE_AT_TITLE', 'Sicherheitsschl&uuml;ssel AT');
define('MODULE_PAYMENT_RATEPAY_RATE_SECURITY_CODE_AT_DESC', 'Ihr von RatePAY zugewiesener Sicherheitsschl&uuml;ssel f&uumlr &Ouml;sterreich');
define('MODULE_PAYMENT_RATEPAY_RATE_MIN_DE_TITLE', 'Minimaler Bestellbetrag DE');
define('MODULE_PAYMENT_RATEPAY_RATE_MIN_DE_DESC', 'Tragen Sie hier den minimalen Bestellbetrag f&uuml; aus Deutschland ein');
define('MODULE_PAYMENT_RATEPAY_RATE_MAX_DE_TITLE', 'Maximaler Bestellbetrag DE');
define('MODULE_PAYMENT_RATEPAY_RATE_MAX_DE_DESC', 'Tragen Sie hier den maximalen Bestellbetrag f&uuml; aus Deutschland ein');
define('MODULE_PAYMENT_RATEPAY_RATE_MIN_AT_TITLE', 'Minimaler Bestellbetrag AT');
define('MODULE_PAYMENT_RATEPAY_RATE_MIN_AT_DESC', 'Tragen Sie hier den minimalen Bestellbetrag f&uuml;r Kunden aus &Ouml;sterreich ein');
define('MODULE_PAYMENT_RATEPAY_RATE_MAX_AT_TITLE', 'Maximaler Bestellbetrag  AT');
define('MODULE_PAYMENT_RATEPAY_RATE_MAX_AT_DESC', 'Tragen Sie hier den maximalen Bestellbetrag f&uuml;r Kunden aus &Ouml;sterreich ein');
define('MODULE_PAYMENT_RATEPAY_RATE_RATEPAY_PRIVACY_URL_DE_TITLE', 'RatePAY ZGB-DSH URL DE');
define('MODULE_PAYMENT_RATEPAY_RATE_RATEPAY_PRIVACY_URL_DE_DESC', 'Zus&auml;tzliche Gesch&auml;ftsbedingungen und Datenschutzerkl&auml;rungen DE');
define('MODULE_PAYMENT_RATEPAY_RATE_RATEPAY_PRIVACY_URL_AT_TITLE', 'RatePAY ZGB-DSH URL AT');
define('MODULE_PAYMENT_RATEPAY_RATE_RATEPAY_PRIVACY_URL_AT_DESC', 'Zus&auml;tzliche Gesch&auml;ftsbedingungen und Datenschutzerkl&auml;rungen AT');
define('MODULE_PAYMENT_RATEPAY_RATE_LOGGING_TITLE', 'Logging aktivieren');
define('MODULE_PAYMENT_RATEPAY_RATE_LOGGING_DESC', 'Loggen Sie alle Transaktionen mit RatePAY');
define('MODULE_PAYMENT_RATEPAY_RATE_MERCHANT_NAME_TITLE', 'Beg&uuml;nstigte Firma');
define('MODULE_PAYMENT_RATEPAY_RATE_MERCHANT_NAME_DESC', 'Der Name Ihrer Firma');
define('MODULE_PAYMENT_RATEPAY_RATE_BANK_NAME_TITLE', 'Kreditinstitut');
define('MODULE_PAYMENT_RATEPAY_RATE_BANK_NAME_DESC', 'Der Name Ihrer Bank');
define('MODULE_PAYMENT_RATEPAY_RATE_SORT_CODE_TITLE', 'Bankleitzahl');
define('MODULE_PAYMENT_RATEPAY_RATE_SORT_CODE_DESC', 'Ihre Bankleitzahl');
define('MODULE_PAYMENT_RATEPAY_RATE_ACCOUNT_NR_TITLE', 'Konto-Nr.');
define('MODULE_PAYMENT_RATEPAY_RATE_ACCOUNT_NR_DESC', 'Ihre Kontonummer');
define('MODULE_PAYMENT_RATEPAY_RATE_SWIFT_TITLE', 'SWIFT BIC');
define('MODULE_PAYMENT_RATEPAY_RATE_SWIFT_DESC', 'Ihre Swift/Bic');
define('MODULE_PAYMENT_RATEPAY_RATE_IBAN_TITLE', 'IBAN');
define('MODULE_PAYMENT_RATEPAY_RATE_IBAN_DESC', 'Ihre IBAN');
define('MODULE_PAYMENT_RATEPAY_RATE_SHOP_OWNER_TITLE', 'Gesch&auml;ftsf&uuml;hrer');
define('MODULE_PAYMENT_RATEPAY_RATE_SHOP_OWNER_DESC', 'Ihr Gesch&auml;ftsf&uuml;hrer');
define('MODULE_PAYMENT_RATEPAY_RATE_SHOP_HR_TITLE', 'Handelsregisternummer');
define('MODULE_PAYMENT_RATEPAY_RATE_SHOP_HR_DESC', 'Ihre Handelsregisternummer');
define('MODULE_PAYMENT_RATEPAY_RATE_SHOP_FON_TITLE', 'Telefonnummer');
define('MODULE_PAYMENT_RATEPAY_RATE_SHOP_FON_DESC', 'Ihre Telefonnummer');
define('MODULE_PAYMENT_RATEPAY_RATE_SHOP_FAX_TITLE', 'Faxnummer');
define('MODULE_PAYMENT_RATEPAY_RATE_SHOP_FAX_DESC', 'Ihre Faxnummer');
define('MODULE_PAYMENT_RATEPAY_RATE_SHOP_PLZ_TITLE', 'PLZ und Ort');
define('MODULE_PAYMENT_RATEPAY_RATE_SHOP_PLZ_DESC', 'Ihre PLZ und Ihr Ort');
define('MODULE_PAYMENT_RATEPAY_RATE_SHOP_STREET_TITLE', 'Strasse und Nummer');
define('MODULE_PAYMENT_RATEPAY_RATE_SHOP_STREET_DESC', 'Ihre Strasse und Ihre Hausnummer');
define('MODULE_PAYMENT_RATEPAY_RATE_SHOP_COURT_TITLE', 'Amtsgericht');
define('MODULE_PAYMENT_RATEPAY_RATE_EXTRA_FIELD_DESC', 'Weitere Informationen die auf der Rechnung angezeigt werden');
define('MODULE_PAYMENT_RATEPAY_RATE_EXTRA_FIELD_TITLE', 'Zusatzfeld Rechnung');
define('MODULE_PAYMENT_RATEPAY_RATE_SHOP_COURT_DESC', 'Ihr zust&auml;ndiges Amtsgericht');
define('MODULE_PAYMENT_RATEPAY_RATE_SORT_ORDER_TITLE', 'Sortierung');
define('MODULE_PAYMENT_RATEPAY_RATE_SORT_ORDER_DESC', 'Die Sortierung dieser Zahlweise');
define('MODULE_PAYMENT_RATEPAY_RATE_ALLOWED_TITLE', 'Erlaubte L&auml;nder');
define('MODULE_PAYMENT_RATEPAY_RATE_ALLOWED_DESC', 'Bitte geben Sie hier alle erlaubten L&auml;nder an.');
define('MODULE_PAYMENT_RATEPAY_RATE_ZONE_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_RATEPAY_RATE_ZONE_DESC', 'Bitte geben Sie hier die erlaubten Zonen an');
define('MODULE_PAYMENT_RATEPAY_RATE_ORDER_STATUS_ID_TITLE', 'Bestellstatus');
define('MODULE_PAYMENT_RATEPAY_RATE_ORDER_STATUS_ID_DESC', 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');
define('MODULE_PAYMENT_RATEPAY_SNIPPET_ID_TITLE', 'device Ident SId');
// Config text and description end
//Ratepay Info
define('RATEPAY_RATE_INFO_1', 'Es gelten die');
define('RATEPAY_RATE_INFO_2', 'zus&auml;tzliche Gesch&auml;ftsbedingungen und der Datenschutzhinweis ');
define('RATEPAY_RATE_INFO_3', ' der RatePAY GmbH');
//End ratepay info
// Checkout
define('RATEPAY_RATE_VIEW_PAYMENT_BIRTHDATE_FORMAT', '(tt.mm.jjjj)');
// Checkout end
// Checkout errors
define('RATEPAY_RATE_ERROR', '*Leider ist eine Bezahlung mit RatePAY nicht m&ouml;glich. Diese Entscheidung ist auf Grundlage einer automatisierten Datenverarbeitung getroffen worden. Einzelheiten finden sie in den zus&auml;tzlichen Allgemeinen Gesch&auml;ftsbedingungen und dem Datenschutzhinweis f&uuml;r RatePAY-Zahlungsarten.');
define('RATEPAY_RATE_ERROR_GATEWAY', '*Leider ist die Verbindung zu RatePAY derzeit nicht m&ouml;glich, bitte versuchen Sie es zu einem sp&auml;teren Zeitpunkt erneut.');
define('RATEPAY_RATE_PHONE_IS_MISSING', '*Geben Sie bitte f&uuml;r die Zahlungsoption RatePAY Rate Ihre Telefonnummer an.');
define('RATEPAY_RATE_DOB_IS_MISSING', '*Geben Sie bitte f&uuml;r die Zahlungsoption RatePAY Rate Ihr Geburtsdatum an.');
define('RATEPAY_RATE_DOB_IS_INVALID', '*Geben Sie bitte f&uuml;r die Zahlungsoption RatePAY Rate Ihr Geburtsdatum in dem Format TT.MM.JJJJ an.');
define('RATEPAY_RATE_DOB_IS_YOUNGER', '*Leider ist eine Zahlung mit RatePAY nicht m&ouml;glich. F&uuml;r die Zahlungsoption RatePAY Rate m&uuml;ssen Sie mindestens 18 Jahre alt sein.');
define('RATEPAY_RATE_COMPANY_IS_MISSING', '*Sie haben eine USt-IdNr. angegeben. Geben Sie bitte f&uuml;r die Zahlungsoption RatePAY Rate den Namen Ihres Unternehmens an.');
define('RATEPAY_RATE_VATID_IS_MISSING', '*Geben Sie bitte f&uuml;r die Zahlungsoption RatePAY Rate die USt-IdNr. Ihres Unternehmens an.');
define('RATEPAY_RATE_AGB_MISSING', 'Sofern Sie unsere Allgemeinen Gesch&auml;ftsbedingungen nicht akzeptieren, k&ouml;nnen wir Ihre Bestellung bedauerlicherweise nicht entgegennehmen!');
define('RATEPAY_RATE_CONDITIONS_IS_MISSING', '*Bitte akzeptieren Sie die Allgemeinen Nutzungsbedingungen um mit RatePAY Rate einzukaufen.');
// Checkout errors end
// Invoice
define('RATEPAY_RATE_PDF_OWNER', 'Gesch&auml;ftsf&uuml;hrer:');
define('RATEPAY_RATE_PDF_FON', 'Telefon:');
define('RATEPAY_RATE_PDF_FAX', 'Fax:');
define('RATEPAY_RATE_PDF_EMAIL', 'E-Mail:');
define('RATEPAY_RATE_PDF_COURT', 'Amtsgericht:');
define('RATEPAY_RATE_PDF_HR', 'HR:');
define('RATEPAY_RATE_PDF_UST', 'USt.-ID-Nr.:');
define('RATEPAY_RATE_PDF_BULL', ' &bull; ');
define('RATEPAY_RATE_PDF_ACCOUNTHOLDER', 'Kontoinhaber:');
define('RATEPAY_RATE_PDF_BANKNAME', 'Bank:');
define('RATEPAY_RATE_PDF_BANKCODENUMBER', 'Bankleitzahl:');
define('RATEPAY_RATE_PDF_ACCOUNTNUMBER', 'Kontonummer:');
define('RATEPAY_RATE_PDF_SWIFTBIC', 'SWIFT-BIC:');
define('RATEPAY_RATE_PDF_IBAN', 'IBAN:');
define('RATEPAY_RATE_PDF_INFO', 'Ihren Ratenplan und alle Informationen zur Zahlung erhalten Sie <u>gesondert per E-Mail.</u>');
define('RATEPAY_RATE_PDF_PAYTRANSFER', 'Bitte nutzen Sie dazu die daf&auml;r eingerichtete Kontoverbindung des H&auml;ndlers:');
define('RATEPAY_RATE_PDF_REFERENCE', 'Verwendungszweck:');
define('RATEPAY_RATE_PDF_ADDITIONALINFO_1', 'Unsere Forderungen haben wir im Rahmen eines laufenden Factoringvertrages an die ');
define('RATEPAY_RATE_PDF_ADDITIONALINFO_2', ' abgetreten. Zahlungen k&ouml;nnen mit schuldbefreiender Wirkung ausschlie&szlig;lich an die ');
define('RATEPAY_RATE_PDF_ADDITIONALINFO_3', ' auf das vereinbarte Konto geleistet werden.');
define('RATEPAY_RATE_PDF_ABOVEARTICLE', 'F&uuml;r Ihren Kauf auf Rate berechnen wir Ihnen folgende Artikel:');
define('RATEPAY_RATE_PDF_DESCRIPTOR', 'RatePAY-Order:');
define('RATEPAY_RATE_PDF_SELECTEDPAYMENT', 'Ihre gew&auml;hlte Zahlungsweise: RatePAY Rate');
define('RATEPAY_RATE_PDF_REFERENCENUMBER', 'Die Referenznummer f&uuml;r evtl. Teillieferungen entnehmen Sie bitte dem aktuellen Ratenplan.');
// Invoice end
