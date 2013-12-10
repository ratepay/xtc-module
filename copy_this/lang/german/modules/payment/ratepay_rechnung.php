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
define('MODULE_PAYMENT_RATEPAY_RATE_TEXT_DESCRIPTION', 'Bieten Sie Ihren Kunden sicheren Rechnungskauf');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_TEXT', 'RatePAY Rechnung');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_TEXT_TITLE', 'RatePAY Rechnung');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_TEXT_DESCRIPTION', '');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_STATUS_TITLE', 'RatePAY aktivieren');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_STATUS_DESC', 'Aktivieren Sie RatePAY Rate');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SANDBOX_TITLE', 'Sandbox');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SANDBOX_DESC', 'Testserver oder Livebetrieb?');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_PROFILE_ID_TITLE', 'Profil ID');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_PROFILE_ID_DESC', 'Ihre von RatePAY zugewiesende ID');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SECURITY_CODE_TITLE', 'Sicherheitsschl&uuml;ssel');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SECURITY_CODE_DESC', 'Ihr von RatePAY zugewiesener Sicherheitsschl&uuml;ssel');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_MIN_TITLE', 'Minimaler Bestellbetrag');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_MIN_DESC', 'Tragen Sie hier den minimalen Bestellbetrag ein');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_MAX_TITLE', 'Maximaler Bestellbetrag');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_MAX_DESC', 'Tragen Sie hier den maximalen Bestellbetrag ein');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_MERCHANT_GTC_URL_TITLE', 'AGB URL');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_MERCHANT_GTC_URL_DESC', 'Die URL zu Ihren AGB');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_RATEPAY_PRIVACY_URL_TITLE', 'RatePAY Datenschutz URL');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_RATEPAY_PRIVACY_URL_DESC', 'Die URL zur RatePAY Datenschutzerkl&auml;rung');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_MERCHANT_PRIVACY_URL_TITLE', 'H&auml;ndler Datenschutz URL');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_MERCHANT_PRIVACY_URL_DESC', 'Die URL zur H&auml;ndler Datenschutzerkl&auml;rung');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_LOGGING_TITLE', 'Logging aktivieren');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_LOGGING_DESC', 'Loggen Sie alle Transaktionen mit RatePAY');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_MERCHANT_NAME_TITLE', 'Beg&uuml;nstigte Firma');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_MERCHANT_NAME_DESC', 'Der Name Ihrer Firma');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_BANK_NAME_TITLE', 'Kreditinstitut');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_BANK_NAME_DESC', 'Der Name Ihrer Bank');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SORT_CODE_TITLE', 'Bankleitzahl');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SORT_CODE_DESC', 'Ihre Bankleitzahl');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_ACCOUNT_NR_TITLE', 'Konto-Nr.');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_ACCOUNT_NR_DESC', 'Ihre Kontonummer');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SWIFT_TITLE', 'SWIFT BIC');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SWIFT_DESC', 'Ihre Swift/Bic');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_IBAN_TITLE', 'IBAN');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_IBAN_DESC', 'Ihre IBAN');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SHOP_OWNER_TITLE', 'Gesch&auml;ftsf&uuml;hrer');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SHOP_OWNER_DESC', 'Ihr Gesch&auml;ftsf&uuml;hrer');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SHOP_HR_TITLE', 'Handelsregisternummer');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SHOP_HR_DESC', 'Ihre Handelsregisternummer');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SHOP_FON_TITLE', 'Telefonnummer');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SHOP_FON_DESC', 'Ihre Telefonnummer');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SHOP_FAX_TITLE', 'Faxnummer');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SHOP_FAX_DESC', 'Ihre Faxnummer');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SHOP_PLZ_TITLE', 'PLZ und Ort');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SHOP_PLZ_DESC', 'Ihre PLZ und Ihr Ort');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SHOP_STREET_TITLE', 'Strasse und Nummer');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SHOP_STREET_DESC', 'Ihre Strasse und Ihre Hausnummer');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SHOP_COURT_TITLE', 'Amtsgericht');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SHOP_COURT_DESC', 'Ihr zust&auml;ndiges Amtsgericht');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_EXTRA_FIELD_TITLE', 'Zusatzfeld Rechnung');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_EXTRA_FIELD_DESC', 'Weitere Informationen die auf der Rechnung angezeigt werden');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_LOGGING_TITLE', 'Logging aktivieren');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_LOGGING_DESC', 'Loggen Sie alle Transaktionen mit RatePAY');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SORT_ORDER_TITLE', 'Sortierung');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_SORT_ORDER_DESC', 'Die Sortierung dieser Zahlweise');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_ALLOWED_TITLE', 'Erlaubte L&auml;nder');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_ALLOWED_DESC', 'Bitte geben Sie hier alle erlaubten L&auml;nder an.');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_ZONE_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_ZONE_DESC', 'Bitte geben Sie hier die erlaubten Zonen an');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_ORDER_STATUS_ID_TITLE', 'Bestellstatus');
define('MODULE_PAYMENT_RATEPAY_RECHNUNG_ORDER_STATUS_ID_DESC', 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');
// Config text and description end
// Ratepay Info
define('RATEPAY_RECHNUNG_INFO_1', 'stellt mit Unterst&uuml;tzung von RatePAY die M&ouml;glichkeit der RatePAY-Rechnung bereit. Sie nehmen damit einen Kauf auf Rechnung vor. Die Rechnung ist innerhalb von 14 Tagen nach Rechnungsdatum zur Zahlung f&auml;llig.');
define('RATEPAY_RECHNUNG_INFO_2', 'RatePAY-Rechnung ist ');
define('RATEPAY_RECHNUNG_INFO_3', 'ab einem Einkaufswert von ');
define('RATEPAY_RECHNUNG_INFO_4', ' bis zu einem Einkaufswert von ');
define('RATEPAY_RECHNUNG_INFO_5', ' m&ouml;glich (jeweils inklusive Mehrwertsteuer und Versandkosten).');
define('RATEPAY_RECHNUNG_INFO_6', 'Bitte beachten Sie, dass RatePAY-Rechnung nur genutzt werden kann, wenn Rechnungs- und Lieferadresse identisch sind und Ihrem privaten Wohnsitz entsprechen (keine Firmen- und keine Postfachadresse). Ihre Adresse muss im Gebiet der Bundesrepublik Deutschland liegen. Bitte gehen Sie gegebenenfalls zur&uuml;ck und korrigieren Sie Ihre Daten. ');
define('RATEPAY_RECHNUNG_INFO_7', 'Ich habe die ');
define('RATEPAY_RECHNUNG_INFO_8', 'Allgemeinen Gesch&auml;ftsbedingungen');
define('RATEPAY_RECHNUNG_INFO_9', ' zur Kenntnis genommen und erkl&auml;re mich mit deren Geltung einverstanden. Au&szlig;erdem erkl&auml;re ich hiermit meine Einwilligung zur Verwendung meiner Daten gem&auml;&szlig; der ');
define('RATEPAY_RECHNUNG_INFO_10', 'RatePAY-Datenschutzerkl&auml;rung');
define('RATEPAY_RECHNUNG_INFO_11', ' und bin insbesondere damit einverstanden, zum Zwecke der Durchf&uuml;hrung des Vertrags &uuml;ber die von mir angebene E-Mail Adresse kontaktiert zu werden.');
define('RATEPAY_RECHNUNG_INFO_12', 'Au&szlig;erdem akzeptiere ich die ');
define('RATEPAY_RECHNUNG_INFO_13', 'H&auml;ndler Datenschutzerkl&auml;rung');
define('RATEPAY_RECHNUNG_AGB_ERROR', 'Bitte akzeptieren Sie die Allgemeinen Nutzungsbedingungen um mit RatePAY Rechnung einzukaufen.');
// Ratepay info end
// Checkout
define('RATEPAY_RECHNUNG_VIEW_PAYMENT_BIRTHDATE_FORMAT', '(tt.mm.jjjj)');
// Checkout end
// Checkout errors
define('RATEPAY_RECHNUNG_ERROR', '*Leider ist eine Bezahlung mit RatePAY nicht m&ouml;glich. Diese Entscheidung ist von RatePAY auf der Grundlage einer automatisierten Datenverarbeitung getroffen worden. Einzelheiten erfahren Sie in der RatePAY-Datenschutzerkl&auml;rung.');
define('RATEPAY_RECHNUNG_ERROR_GATEWAY', '*Leider ist die Verbindung zu RatePAY derzeit nicht m&ouml;glich, bitte versuchen Sie es zu einem sp&auml;teren Zeitpunkt erneut.');
define('RATEPAY_RECHNUNG_PHONE_IS_MISSING', '*Geben Sie bitte f&uuml;r die Zahlungsoption RatePAY Rechnung Ihre Telefonnummer an.');
define('RATEPAY_RECHNUNG_DOB_IS_MISSING', '*Geben Sie bitte f&uuml;r die Zahlungsoption RatePAY Rechnung Ihr Geburtsdatum an.');
define('RATEPAY_RECHNUNG_DOB_IS_INVALID', '*Geben Sie bitte f&uuml;r die Zahlungsoption RatePAY Rechnung Ihr Geburtsdatum in dem Format TT.MM.JJJJ an.');
define('RATEPAY_RECHNUNG_DOB_IS_YOUNGER', '*Leider ist eine Zahlung mit RatePAY nicht m&ouml;glich. F&uuml;r die Zahlungsoption RatePAY Rechnung m&uuml;ssen Sie mindestens 18 Jahre alt sein.');
define('RATEPAY_RECHNUNG_COMPANY_IS_MISSING', '*Sie haben eine USt-IdNr. angegeben. Geben Sie bitte f&uuml;r die Zahlungsoption RatePAY Rechnung den Namen Ihres Unternehmens an.');
define('RATEPAY_RECHNUNG_VATID_IS_MISSING', '*Geben Sie bitte f&uuml;r die Zahlungsoption RatePAY Rechnung die USt-IdNr. Ihres Unternehmens an.');
// Checkout errors end
// Invoice
define('RATEPAY_RECHNUNG_PDF_OWNER', 'Gesch&auml;ftsf&uuml;hrer:');
define('RATEPAY_RECHNUNG_PDF_FON', 'Telefon:');
define('RATEPAY_RECHNUNG_PDF_FAX', 'Fax:');
define('RATEPAY_RECHNUNG_PDF_EMAIL', 'E-Mail:');
define('RATEPAY_RECHNUNG_PDF_COURT', 'Amtsgericht:');
define('RATEPAY_RECHNUNG_PDF_HR', 'HR:');
define('RATEPAY_RECHNUNG_PDF_UST', 'USt.-ID-Nr.:');
define('RATEPAY_RECHNUNG_PDF_BULL', ' &bull; ');
define('RATEPAY_RECHNUNG_PDF_ACCOUNTHOLDER', 'Kontoinhaber:');
define('RATEPAY_RECHNUNG_PDF_BANKNAME', 'Bank:');
define('RATEPAY_RECHNUNG_PDF_BANKCODENUMBER', 'Bankleitzahl:');
define('RATEPAY_RECHNUNG_PDF_ACCOUNTNUMBER', 'Kontonummer:');
define('RATEPAY_RECHNUNG_PDF_SWIFTBIC', 'SWIFT-BIC:');
define('RATEPAY_RECHNUNG_PDF_IBAN', 'IBAN:');
define('RATEPAY_RECHNUNG_PDF_INTERNATIONALDESC', 'F&uuml;r den internationalen Zahlungstransfer:');
define('RATEPAY_RECHNUNG_PDF_PAYTRANSFER', 'Bitte &uuml;berweisen Sie den oben aufgef&uuml;hrten Betrag auf folgendes Konto:');
define('RATEPAY_RECHNUNG_PDF_PAYUNTIL', 'Es gelten folgende Zahlungsbedingungen: 14 Tage nach Rechnungsdatum ohne Abzug');
define('RATEPAY_RECHNUNG_PDF_REFERENCE', 'Verwendungszweck:');
define('RATEPAY_RECHNUNG_PDF_ADDITIONALINFO_1', 'Der Verk&auml;ufer hat die f&auml;llige Kaufpreisforderung aus Ihrer Bestellung einschlie&szlig;lich etwaiger Nebenforderungen an die RatePAY GmbH abgetreten. ');
define('RATEPAY_RECHNUNG_PDF_ADDITIONALINFO_2', 'Forderungsinhaber ist damit RatePAY GmbH. Eine schuldbefreiende Leistung durch Zahlung ist gem&auml;&szlig; &sect; 407 B&uuml;rgerliches Gesetzbuch durch');
define('RATEPAY_RECHNUNG_PDF_ADDITIONALINFO_3', 'Sie nur an die RatePAY GmbH m&ouml;glich.');
define('RATEPAY_RECHNUNG_PDF_ABOVEARTICLE', 'Für Ihren Kauf auf Rechnung berechnen wir Ihnen folgende Artikel:');
define('RATEPAY_RECHNUNG_PDF_DESCRIPTOR', 'RatePAY-Order:');
define('RATEPAY_RECHNUNG_PDF_SELECTEDPAYMENT', 'Ihre gew&auml;hlte Zahlungsweise: RatePAY Rechnung');
// Invoice end