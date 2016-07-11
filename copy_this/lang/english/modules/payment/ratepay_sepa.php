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
define('MODULE_PAYMENT_RATEPAY_RATE_TEXT_DESCRIPTION', 'Bieten Sie Ihren Kunden sicheren SEPA Kauf');
define('MODULE_PAYMENT_RATEPAY_SEPA_TEXT', 'RatePAY Lastschrift');
define('MODULE_PAYMENT_RATEPAY_SEPA_TEXT_TITLE', 'RatePAY Lastschrift');
define('MODULE_PAYMENT_RATEPAY_SEPA_TEXT_DESCRIPTION', '');
define('MODULE_PAYMENT_RATEPAY_SEPA_STATUS_TITLE', 'Zahlart aktivieren');
define('MODULE_PAYMENT_RATEPAY_SEPA_STATUS_DESC', 'Aktivieren Sie RatePAY Lastschrift');
define('MODULE_PAYMENT_RATEPAY_SEPA_SANDBOX_TITLE', 'Sandbox');
define('MODULE_PAYMENT_RATEPAY_SEPA_SANDBOX_DESC', 'Testserver oder Livebetrieb?');
define('MODULE_PAYMENT_RATEPAY_SEPA_PROFILE_ID_DE_TITLE', 'Profil ID DE');
define('MODULE_PAYMENT_RATEPAY_SEPA_PROFILE_ID_DE_DESC', 'Ihre von RatePAY zugewiesende ID f&uumlr Deutschland');
define('MODULE_PAYMENT_RATEPAY_SEPA_SECURITY_CODE_DE_TITLE', 'Sicherheitsschl&uuml;ssel DE');
define('MODULE_PAYMENT_RATEPAY_SEPA_SECURITY_CODE_DE_DESC', 'Ihr von RatePAY zugewiesener Sicherheitsschl&uuml;ssel f&uumlr Deutschland');
define('MODULE_PAYMENT_RATEPAY_SEPA_PROFILE_ID_AT_TITLE', 'Profil ID AT');
define('MODULE_PAYMENT_RATEPAY_SEPA_PROFILE_ID_AT_DESC', 'Ihre von RatePAY zugewiesende ID f&uumlr &Ouml;sterreich');
define('MODULE_PAYMENT_RATEPAY_SEPA_SECURITY_CODE_AT_TITLE', 'Sicherheitsschl&uuml;ssel AT');
define('MODULE_PAYMENT_RATEPAY_SEPA_SECURITY_CODE_AT_DESC', 'Ihr von RatePAY zugewiesener Sicherheitsschl&uuml;ssel f&uumlr &Ouml;sterreich');
define('MODULE_PAYMENT_RATEPAY_SEPA_MIN_DE_TITLE', 'Minimaler Bestellbetrag DE');
define('MODULE_PAYMENT_RATEPAY_SEPA_MIN_DE_DESC', 'Tragen Sie hier den minimalen Bestellbetrag f&uuml; aus Deutschland ein');
define('MODULE_PAYMENT_RATEPAY_SEPA_MAX_DE_TITLE', 'Maximaler Bestellbetrag DE');
define('MODULE_PAYMENT_RATEPAY_SEPA_MAX_DE_DESC', 'Tragen Sie hier den maximalen Bestellbetrag f&uuml; aus Deutschland ein');
define('MODULE_PAYMENT_RATEPAY_SEPA_MIN_AT_TITLE', 'Minimaler Bestellbetrag AT');
define('MODULE_PAYMENT_RATEPAY_SEPA_MIN_AT_DESC', 'Tragen Sie hier den minimalen Bestellbetrag f&uuml;r Kunden aus &Ouml;sterreich ein');
define('MODULE_PAYMENT_RATEPAY_SEPA_MAX_AT_TITLE', 'Maximaler Bestellbetrag  AT');
define('MODULE_PAYMENT_RATEPAY_SEPA_MAX_AT_DESC', 'Tragen Sie hier den maximalen Bestellbetrag f&uuml;r Kunden aus &Ouml;sterreich ein');
define('MODULE_PAYMENT_RATEPAY_SEPA_B2B_AT_TITLE', 'Business to Business AT');
define('MODULE_PAYMENT_RATEPAY_SEPA_B2B_AT_DESC', 'B2B verkauf in &Ouml;sterreich erlauben');
define('MODULE_PAYMENT_RATEPAY_SEPA_B2B_DE_TITLE', 'Business to Business DE');
define('MODULE_PAYMENT_RATEPAY_SEPA_B2B_DE_DESC', 'B2B verkauf in Deutschland erlauben');
define('MODULE_PAYMENT_RATEPAY_SEPA_RATEPAY_PRIVACY_URL_DE_TITLE', 'RatePAY ZGB-DSH URL DE');
define('MODULE_PAYMENT_RATEPAY_SEPA_RATEPAY_PRIVACY_URL_DE_DESC', 'Zus&auml;tzliche Gesch&auml;ftsbedingungen und Datenschutzerkl&auml;rungen DE');
define('MODULE_PAYMENT_RATEPAY_SEPA_RATEPAY_PRIVACY_URL_AT_TITLE', 'RatePAY ZGB-DSH URL AT');
define('MODULE_PAYMENT_RATEPAY_SEPA_RATEPAY_PRIVACY_URL_AT_DESC', 'Zus&auml;tzliche Gesch&auml;ftsbedingungen und Datenschutzerkl&auml;rungen AT');
define('MODULE_PAYMENT_RATEPAY_SEPA_LOGGING_TITLE', 'Logging aktivieren');
define('MODULE_PAYMENT_RATEPAY_SEPA_LOGGING_DESC', 'Loggen Sie alle Transaktionen mit RatePAY');
define('MODULE_PAYMENT_RATEPAY_SEPA_MERCHANT_NAME_TITLE', 'Beg&uuml;nstigte Firma');
define('MODULE_PAYMENT_RATEPAY_SEPA_MERCHANT_NAME_DESC', 'Der Name Ihrer Firma');
define('MODULE_PAYMENT_RATEPAY_SEPA_BANK_NAME_TITLE', 'Kreditinstitut');
define('MODULE_PAYMENT_RATEPAY_SEPA_BANK_NAME_DESC', 'Der Name Ihrer Bank');
define('MODULE_PAYMENT_RATEPAY_SEPA_SORT_CODE_TITLE', 'Bankleitzahl');
define('MODULE_PAYMENT_RATEPAY_SEPA_SORT_CODE_DESC', 'Ihre Bankleitzahl');
define('MODULE_PAYMENT_RATEPAY_SEPA_ACCOUNT_NR_TITLE', 'Konto-Nr.');
define('MODULE_PAYMENT_RATEPAY_SEPA_ACCOUNT_NR_DESC', 'Ihre Kontonummer');
define('MODULE_PAYMENT_RATEPAY_SEPA_SWIFT_TITLE', 'SWIFT BIC');
define('MODULE_PAYMENT_RATEPAY_SEPA_SWIFT_DESC', 'Ihre Swift/Bic');
define('MODULE_PAYMENT_RATEPAY_SEPA_IBAN_TITLE', 'IBAN');
define('MODULE_PAYMENT_RATEPAY_SEPA_IBAN_DESC', 'Ihre IBAN');
define('MODULE_PAYMENT_RATEPAY_SEPA_SHOP_OWNER_TITLE', 'Gesch&auml;ftsf&uuml;hrer');
define('MODULE_PAYMENT_RATEPAY_SEPA_SHOP_OWNER_DESC', 'Ihr Gesch&auml;ftsf&uuml;hrer');
define('MODULE_PAYMENT_RATEPAY_SEPA_SHOP_HR_TITLE', 'Handelsregisternummer');
define('MODULE_PAYMENT_RATEPAY_SEPA_SHOP_HR_DESC', 'Ihre Handelsregisternummer');
define('MODULE_PAYMENT_RATEPAY_SEPA_SHOP_FON_TITLE', 'Telefonnummer');
define('MODULE_PAYMENT_RATEPAY_SEPA_SHOP_FON_DESC', 'Ihre Telefonnummer');
define('MODULE_PAYMENT_RATEPAY_SEPA_SHOP_FAX_TITLE', 'Faxnummer');
define('MODULE_PAYMENT_RATEPAY_SEPA_SHOP_FAX_DESC', 'Ihre Faxnummer');
define('MODULE_PAYMENT_RATEPAY_SEPA_SHOP_PLZ_TITLE', 'PLZ und Ort');
define('MODULE_PAYMENT_RATEPAY_SEPA_SHOP_PLZ_DESC', 'Ihre PLZ und Ihr Ort');
define('MODULE_PAYMENT_RATEPAY_SEPA_SHOP_STREET_TITLE', 'Strasse und Nummer');
define('MODULE_PAYMENT_RATEPAY_SEPA_SHOP_STREET_DESC', 'Ihre Strasse und Ihre Hausnummer');
define('MODULE_PAYMENT_RATEPAY_SEPA_SHOP_COURT_TITLE', 'Amtsgericht');
define('MODULE_PAYMENT_RATEPAY_SEPA_SHOP_COURT_DESC', 'Ihr zust&auml;ndiges Amtsgericht');
define('MODULE_PAYMENT_RATEPAY_SEPA_EXTRA_FIELD_TITLE', 'Zusatzfeld Lastschrift');
define('MODULE_PAYMENT_RATEPAY_SEPA_EXTRA_FIELD_DESC', 'Weitere Informationen die auf der Lastschrift angezeigt werden');
define('MODULE_PAYMENT_RATEPAY_SEPA_LOGGING_TITLE', 'Logging aktivieren');
define('MODULE_PAYMENT_RATEPAY_SEPA_LOGGING_DESC', 'Loggen Sie alle Transaktionen mit RatePAY');
define('MODULE_PAYMENT_RATEPAY_SEPA_SORT_ORDER_TITLE', 'Sortierung');
define('MODULE_PAYMENT_RATEPAY_SEPA_SORT_ORDER_DESC', 'Die Sortierung dieser Zahlweise');
define('MODULE_PAYMENT_RATEPAY_SEPA_ALLOWED_TITLE', 'Erlaubte L&auml;nder');
define('MODULE_PAYMENT_RATEPAY_SEPA_ALLOWED_DESC', 'Bitte geben Sie hier alle erlaubten L&auml;nder an.');
define('MODULE_PAYMENT_RATEPAY_SEPA_ZONE_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_RATEPAY_SEPA_ZONE_DESC', 'Bitte geben Sie hier die erlaubten Zonen an');
define('MODULE_PAYMENT_RATEPAY_SEPA_ORDER_STATUS_ID_TITLE', 'Bestellstatus');
define('MODULE_PAYMENT_RATEPAY_SEPA_ORDER_STATUS_ID_DESC', 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');
define('MODULE_PAYMENT_RATEPAY_SNIPPET_ID_TITLE', 'device Ident SId');
// Config text and description end
// Ratepay Info

define('RATEPAY_SEPA_DATA_1', 'RatePAY GmbH, Schl&uuml;terstr. 39, 10629 Berlin');
define('RATEPAY_SEPA_DATA_2', 'Gl&auml;ubiger-ID: DE39RPY00000568463');
define('RATEPAY_SEPA_DATA_3', 'Mandatsreferenz: (wird nach Kaufabschluss &uuml;bermittelt)');

define('RATEPAY_SEPA_INFO_1', 'Ich willige hiermit in die Weiterleitung meiner Daten an RatePAY GmbH, Schl&uuml;terstr. 39, 10629 Berlin gem&auml;&szlig;');
define('RATEPAY_SEPA_INFO_2', 'RatePAY-Datenschutzerkl&auml;rung');
define('RATEPAY_SEPA_INFO_3', ' ein und erm&auml;chtige diese, mit diesem Kaufvertrag in Zusammenhang stehende Zahlungen von meinem o.a. Konto mittels Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an, die von RatePAY GmbH auf mein Konto gezogenen Lastschriften einzul&ouml;sen.');
define('RATEPAY_SEPA_INFO_4', 'Hinweis: ');
define('RATEPAY_SEPA_INFO_5', 'Nach Zustandekommen des Vertrags wird mir die Mandatsreferenz von RatePAY mitgeteilt. Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen.');
define('RATEPAY_SEPA_INFO_6', 'Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen.');
define('RATEPAY_SEPA_INFO_7', 'Es gelten dabei die mit meinem Kreditinstitut vereinbarten Bedingungen.');

define('RATEPAY_SEPA_ACCOUNT_HOLDER', 'Kontoinhaber');
define('RATEPAY_SEPA_ACCOUNT_NUMBER', 'Kontonummer/IBAN');
define('RATEPAY_SEPA_ACCOUNT_SORT_CODE', 'Bankleitzahl/BIC');
define('RATEPAY_SEPA_ACCOUNT_BANK_NAME', 'Kreditinstitut');

// Ratepay info end
// Checkout
define('RATEPAY_SEPA_VIEW_PAYMENT_BIRTHDATE_FORMAT', '(tt.mm.jjjj)');
// Checkout end
// Checkout errors
define('RATEPAY_SEPA_ERROR', '*Leider ist eine Bezahlung mit RatePAY nicht m&ouml;glich. Diese Entscheidung ist auf Grundlage einer automatisierten Datenverarbeitung getroffen worden. Einzelheiten finden sie in den zus&auml;tzlichen Allgemeinen Gesch&auml;ftsbedingungen und dem Datenschutzhinweis f&uuml;r RatePAY-Zahlungsarten.');
define('RATEPAY_SEPA_ERROR_GATEWAY', '*Leider ist die Verbindung zu RatePAY derzeit nicht m&ouml;glich, bitte versuchen Sie es zu einem sp&auml;teren Zeitpunkt erneut.');
define('RATEPAY_SEPA_PHONE_IS_MISSING', '*Geben Sie bitte f&uuml;r die Zahlungsoption RatePAY Lastschrift Ihre Telefonnummer an.');
define('RATEPAY_SEPA_DOB_IS_MISSING', '*Geben Sie bitte f&uuml;r die Zahlungsoption RatePAY Lastschrift Ihr Geburtsdatum an.');
define('RATEPAY_SEPA_DOB_IS_INVALID', '*Geben Sie bitte f&uuml;r die Zahlungsoption RatePAY Lastschrift Ihr Geburtsdatum in dem Format TT.MM.JJJJ an.');
define('RATEPAY_SEPA_DOB_IS_YOUNGER', '*Leider ist eine Zahlung mit RatePAY nicht m&ouml;glich. F&uuml;r die Zahlungsoption RatePAY Lastschrift m&uuml;ssen Sie mindestens 18 Jahre alt sein.');
define('RATEPAY_SEPA_COMPANY_IS_MISSING', '*Sie haben eine USt-IdNr. angegeben. Geben Sie bitte f&uuml;r die Zahlungsoption RatePAY Lastschrift den Namen Ihres Unternehmens an.');
define('RATEPAY_SEPA_VATID_IS_MISSING', '*Geben Sie bitte f&uuml;r die Zahlungsoption RatePAY Lastschrift die USt-IdNr. Ihres Unternehmens an.');
define('RATEPAY_SEPA_CONDITIONS_IS_MISSING', '*Bitte akzeptieren Sie die Allgemeinen Nutzungsbedingungen um mit RatePAY Lastschrift einzukaufen.');
define('RATEPAY_SEPA_ACCOUNT_HOLDER_IS_MISSING', '*Bitte geben Sie den Kontoinhaber an um mit RatePAY Lastschrift einzukaufen.');
define('RATEPAY_SEPA_ACCOUNT_NUMBER_IS_MISSING', '*Bitte geben Sie Ihre Kontonummer/IBAN an um mit RatePAY Lastschrift einzukaufen.');
define('RATEPAY_SEPA_SORT_CODE_IS_MISSING', '*Bitte geben Sie Ihre Bankleitzahl/BIC an um mit RatePAY Lastschrift einzukaufen.');
define('RATEPAY_SEPA_BANK_NAME_IS_MISSING', '*Bitte geben Sie Ihr Kreditinstitut an um mit RatePAY Lastschrift einzukaufen.');
define('RATEPAY_SEPA_SORT_CODE_IS_IBAN_AND_BLZ_PROVIDED', '*Bitte geben Sie keine Bankleitzahl ein wenn Sie eine IBAN verwenden.');
define('RATEPAY_SEPA_SORT_CODE_IS_WRONG_LENGTH', '*Ihre Bankleitzahl muss 8 Ziffern enthalten.');
define('RATEPAY_SEPA_SORT_CODE_IS_NOT_NUMERIC', '*Ihre Bankleitzahl darf nur aus Ziffern bestehen.');
define('RATEPAY_SEPA_ACCOUNT_NUMBER_IS_AT_WRONG_LENGTH', '*Ihre IBAN muss genau 20 Zeichen enthalten.');
define('RATEPAY_SEPA_ACCOUNT_NUMBER_IS_DE_WRONG_LENGTH', '*Ihre IBAN muss genau 22 Zeichen enthalten.');
define('RATEPAY_SEPA_ACCOUNT_NUMBER_IS_KONTO_NR_NOT_NUMERIC', '*Ihre Kontonummer darf nur Ziffern enthalten.');
define('RATEPAY_SEPA_ACCOUNT_NUMBER_IS_IBAN_INVALID', '*Ihre IBAN darf nur 2 Buchstaben enthalten.');
// Checkout errors end
