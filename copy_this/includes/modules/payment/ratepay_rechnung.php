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

require_once('abstract/ratepay_abstract.php');

/**
 * RatePAY Rechnung payment class
 */
class ratepay_rechnung extends ratepay_abstract
{

    /**
     * Payment code
     * 
     * @var string
     */
    public $code;
    
    /**
     * Payment title module admin
     * 
     * @var string
     */
    public $title;
    
    /**
     * Payment title checkout
     * 
     * @var string
     */
    public $public_title;
    
    /**
     * Payment description
     * 
     * @var string 
     */
    public $description;
    
    /**
     * Payment active flag
     * 
     * @var boolean
     */
    public $enabled;
    
    /**
     * Payment allowed flag
     * 
     * @var boolean
     */
    public $check;
    
    /**
     * Config entry "RatePAY Profile ID"
     * 
     * @var string
     */
    public $profileId;
    
    /**
     * Config entry "RatePAY Securitycode"
     * 
     * @var string
     */
    public $securityCode;
    
    /**
     * Config entry sandbox flag
     * 
     * @var boolean
     */
    public $sandbox;
    
    /**
     * Config entry logging flag
     * 
     * @var boolean
     */
    public $logging;
    
    /**
     * Module version
     * 
     * @var string
     */
    public $version;
    
    /**
     * Shop system
     * 
     * @var string
     */
    public $shopSystem;
    
    /**
     * Shop version
     * 
     * @var string
     */
    public $shopVersion;
    
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
     * Merchant privacy url
     * 
     * @var string
     */
    public $merchantPrivacyUrl;
    
    /**
     * Merchant gtc url
     * 
     * @var string
     */
    public $merchantGtcUrl;
    
    /**
     * RatePAY privacy url
     * 
     * @var string
     */
    public $ratepayPrivacyUrl;
    
    /**
     * b2b de flag
     * @var boolan
     */
    public $b2bDe;
    
    /**
     * This constructor set's all properties for the ratepay_rechnung object
     */
    public function __construct()
    {
        global $order;
        
        $this->code               = 'ratepay_rechnung';
        $this->version            = '2.2.3';
        $this->shopVersion        = str_replace(' ','',str_replace("xt:Commerce v", "", PROJECT_VERSION));
        $this->shopSystem         = 'xt:Commerce';
        $this->title              = MODULE_PAYMENT_RATEPAY_RECHNUNG_TEXT . " (" . $this->version . ")";
        $this->public_title       = MODULE_PAYMENT_RATEPAY_RECHNUNG_TEXT_TITLE;
        $this->description        = utf8_decode(MODULE_PAYMENT_RATEPAY_RECHNUNG_TEXT_DESCRIPTION);
        $this->enabled            = (MODULE_PAYMENT_RATEPAY_RECHNUNG_STATUS == 'True') ? true : false;
        $this->minDe              = MODULE_PAYMENT_RATEPAY_RECHNUNG_MIN_DE;
        $this->maxDe              = MODULE_PAYMENT_RATEPAY_RECHNUNG_MAX_DE;
        $this->minAt              = MODULE_PAYMENT_RATEPAY_RECHNUNG_MIN_AT;
        $this->maxAt              = MODULE_PAYMENT_RATEPAY_RECHNUNG_MAX_AT;
        $this->minCh              = MODULE_PAYMENT_RATEPAY_RECHNUNG_MIN_CH;
        $this->maxCh              = MODULE_PAYMENT_RATEPAY_RECHNUNG_MAX_CH;
        $this->b2bDe              = (MODULE_PAYMENT_RATEPAY_RECHNUNG_B2B_DE == 'True') ? true : false;
        $this->sandbox            = (MODULE_PAYMENT_RATEPAY_RECHNUNG_SANDBOX == 'True') ? true : false;
        $this->logging            = (MODULE_PAYMENT_RATEPAY_RECHNUNG_LOGGING == 'True') ? true : false;
        $this->sort_order         = MODULE_PAYMENT_RATEPAY_RECHNUNG_SORT_ORDER;
        
        $country = $order->billing['country']['iso_code_2'];
        if (!is_array($order->billing['country'])) {
            $country = rpSession::getRpSessionEntry('customers_country_code');
        }
        
        if (!is_null(rpSession::getRpSessionEntry('orderId'))) {
            $country = rpDb::getRatepayOrderDataEntry(rpSession::getRpSessionEntry('orderId'), 'customers_country_code');
        } 
        
        if (!is_null(rpSession::getRpSessionEntry('countryCode'))) {
            $country = rpSession::getRpSessionEntry('countryCode');
        }
        
        $this->_setCredentials($country);
        
        if ((int) MODULE_PAYMENT_RATEPAY_RECHNUNG_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_RATEPAY_RECHNUNG_ORDER_STATUS_ID;
        }
        
        if (is_object($order)) {
            $this->update_status();
        }
    }
    
    public function selection()
    {
        global $order;
        $display = parent::selection();
        
        if (!is_null($display)) {
            $minVarName = 'min' . ucfirst(strtolower($order->billing['country']['iso_code_2']));
            $maxVarName = 'max' . ucfirst(strtolower($order->billing['country']['iso_code_2']));

            $privacy = '';
            $privacyConstant = 'MODULE_PAYMENT_' . strtoupper($this->code) . '_RATEPAY_PRIVACY_URL_' . strtoupper($order->billing['country']['iso_code_2']);
            if (defined($privacyConstant)) {
                $privacy = constant($privacyConstant);
            }
            
            $smarty = new Smarty();
            /* BEGINN OF DEVICE FINGERPRINT CODE */
            if (!rpSession::getRpSessionEntry('RATEPAY_DFP_TOKEN') && rpDb::getRpDfpSId()) {
                $ratepay_dfp_token = md5($order->info['total'] . microtime());
                rpSession::setRpSessionEntry('RATEPAY_DFP_TOKEN', $ratepay_dfp_token);
                $smarty->assign('RATEPAY_DFP_TOKEN', $ratepay_dfp_token);
                $smarty->assign('RATEPAY_DFP_SNIPPET_ID', rpDb::getRpDfpSId());
            }
            /* END OF DEVICE FINGERPRINT CODE */
            //CS Aenderung des Value von $display['module'] fuer die Ausgabe
            $display['module'] =  $this->public_title;
            $display['fields'][] = array('title' => '', 'field' => $smarty->fetch(CURRENT_TEMPLATE . '/module/ratepay_rechnung.html'));
        }
        
        return $display;
    }
    
    public function confirmation()
    {
        return false;
    }

    /**
     * Updates the payment status status
     */
    public function update_status() 
    {
        global $order;
        if (($this->enabled == true) && ((int) MODULE_PAYMENT_RATEPAY_RECHNUNG_ZONE > 0)) {
            $check_flag = false;
            $check_query = xtc_db_query("SELECT zone_id from "
                    . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '"
                    . MODULE_PAYMENT_RATEPAY_RECHNUNG_ZONE . "' and zone_country_id = '"
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

            if (!$check_flag) {
                $this->enabled = false;
            }
        }
    }

    /**
     * Checks if RatePAY Rechnung is enabled.
     *
     * @return boolean
     */
    public function check() 
    {
        if (!isset($this->check)) {
            $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_RATEPAY_RECHNUNG_STATUS'");
            $this->check = xtc_db_num_rows($check_query);
        }
        
        return $this->check;
    }

    /**
     * Install routine, inserts all module entrys
     */
    public function install() 
    {
        $this->_installRatepayPaidState();
        if (xtc_db_num_rows(xtc_db_query("SHOW TABLES LIKE 'ratepay_rechnung_orders'")) == 0) {
            xtc_db_query(
                    "CREATE TABLE `ratepay_rechnung_orders`("
                  . " `id` int(11) NOT NULL auto_increment,"
                  . " `order_number` varchar(32) NOT NULL,"
                  . " `transaction_id` varchar(64) NOT NULL,"
                  . " `transaction_short_id` varchar(64) NOT NULL,"
                  . " `customers_birth` varchar(64) NOT NULL,"
                  . " `fax` varchar(64) NOT NULL,"
                  . " `gender` varchar(1) NOT NULL,"
                  . " `customers_country_code` varchar(2) NOT NULL,"
                  . " `descriptor` varchar(20),"
                  . " PRIMARY KEY  (`id`)"
                  . " ) ENGINE=MyISAM AUTO_INCREMENT=1;"
            );
        }
        
        if (xtc_db_num_rows(xtc_db_query("SHOW TABLES LIKE 'ratepay_rechnung_items'")) == 0) {
            xtc_db_query(
                    "CREATE TABLE `ratepay_rechnung_items` ("
                  . " `id` INT NOT NULL AUTO_INCREMENT,"
                  . " `order_number` VARCHAR( 255 ) NOT NULL ,"
                  . " `article_number` VARCHAR( 255 ) NOT NULL ,"
                  . " `article_name` VARCHAR(255) NOT NULL,"
                  . " `ordered` INT NOT NULL DEFAULT '1',"
                  . " `shipped` INT NOT NULL DEFAULT '0',"
                  . " `cancelled` INT NOT NULL DEFAULT '0',"
                  . " `returned` INT NOT NULL DEFAULT '0',"
                  . " `unit_price_gross` decimal(14,8) NOT NULL DEFAULT '0',"
                  . " `tax_rate` decimal(4,2) NOT NULL DEFAULT '0',"
                  . " PRIMARY KEY  (`id`)"
                  . " ) ENGINE=MyISAM AUTO_INCREMENT=1;"
            );
        }
        
        if (xtc_db_num_rows(xtc_db_query("SHOW TABLES LIKE 'ratepay_rechnung_history'")) == 0) {
            xtc_db_query(
                    "CREATE TABLE `ratepay_rechnung_history` ("
                  . " `id` INT NOT NULL AUTO_INCREMENT,"
                  . " `order_number` VARCHAR( 255 ) NOT NULL ,"
                  . " `article_number` VARCHAR( 255 ) NOT NULL ,"
                  . " `article_name` VARCHAR( 255 ) NOT NULL ,"
                  . " `quantity` INT NOT NULL,"
                  . " `method` VARCHAR( 40 ) NOT NULL,"
                  . " `submethod` VARCHAR( 40 ) NOT NULL DEFAULT '',"
                  . " `date` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,"
                  . " PRIMARY KEY  (`id`)"
                  . " ) ENGINE=MyISAM AUTO_INCREMENT=1;"
            );
        }
        
        if (xtc_db_num_rows(xtc_db_query("SHOW TABLES LIKE 'ratepay_log'")) == 0) {
            xtc_db_query(
                    "CREATE TABLE `ratepay_log` ("
                  . " `id` INT NOT NULL AUTO_INCREMENT,"
                  . " `order_number` VARCHAR( 255 ) NOT NULL,"
                  . " `transaction_id` VARCHAR( 255 ) NOT NULL,"
                  . " `payment_method` VARCHAR( 40 ) NOT NULL,"
                  . " `payment_type` VARCHAR( 40 ) NOT NULL,"
                  . " `payment_subtype` VARCHAR( 40 ) NOT NULL,"
                  . " `result` VARCHAR( 40 ) NOT NULL,"
                  . " `request` MEDIUMTEXT NOT NULL,"
                  . " `response` MEDIUMTEXT NOT NULL,"
                  . " `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,"
                  . " `result_code` VARCHAR( 10 ) NOT NULL,"
                  . " `reason` VARCHAR( 255 ) NOT NULL DEFAULT '',"
                  . " PRIMARY KEY  (`id`)"
                  . " ) ENGINE=MyISAM AUTO_INCREMENT=1;"
            );
        }
        
        if (xtc_db_num_rows(xtc_db_query("show columns from admin_access like 'ratepay_%'")) == 0) {
            xtc_db_query("ALTER TABLE admin_access ADD ratepay_logging INT(1) NOT NULL DEFAULT '0'");
            xtc_db_query("ALTER TABLE admin_access ADD delete_logging INT(1) NOT NULL DEFAULT '0'");
            xtc_db_query("ALTER TABLE admin_access ADD ratepay_log INT(1) NOT NULL DEFAULT '0'");
            xtc_db_query("ALTER TABLE admin_access ADD ratepay_order INT(1) NOT NULL DEFAULT '0'");
            xtc_db_query("ALTER TABLE admin_access ADD ratepay_order_script INT(1) NOT NULL DEFAULT '0'");
            xtc_db_query("ALTER TABLE admin_access ADD ratepay_rechnung_print_order INT(1) NOT NULL DEFAULT '0'");
            xtc_db_query("ALTER TABLE admin_access ADD ratepay_rate_print_order INT(1) NOT NULL DEFAULT '0'");
            xtc_db_query("UPDATE admin_access SET ratepay_logging = '1', delete_logging = '1', ratepay_log = '1', ratepay_order = '1', ratepay_order_script = '1' WHERE customers_id= '1' OR customers_id = 'groups'");
        } 
        
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_STATUS', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_SANDBOX', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_LOGGING', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_PROFILE_ID_DE', '', '6', '3', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_SECURITY_CODE_DE', '', '6', '3', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_PROFILE_ID_AT', '', '6', '3', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_SECURITY_CODE_AT', '', '6', '3', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_PROFILE_ID_CH', '', '6', '3', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_SECURITY_CODE_CH', '', '6', '3', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_MIN_DE', '', '6', '3', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_MAX_DE', '', '6', '3', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_MIN_AT', '', '6', '3', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_MAX_AT', '', '6', '3', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_MIN_CH', '', '6', '3', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_MAX_CH', '', '6', '3', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_B2B_DE', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_RATEPAY_PRIVACY_URL_DE', '', '6', '3', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_RATEPAY_PRIVACY_URL_AT', '', '6', '3', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_RATEPAY_PRIVACY_URL_CH', '', '6', '3', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_ALLOWED', '', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_ORDER_STATUS_ID', '0', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_RECHNUNG_SORT_ORDER', '0', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_RATEPAY_SNIPPET_ID', '', '6', '3', now())");
    }

    /**
     * Removes all RatePAY Rechnung module entrys
     */
    public function remove() 
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    /**
     * All RatePAY Rechnung module keys
     *
     * @return array
     */
    public function keys() 
    {
        return array (
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_STATUS',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_SANDBOX',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_LOGGING',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_PROFILE_ID_DE',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_SECURITY_CODE_DE',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_PROFILE_ID_AT',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_SECURITY_CODE_AT',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_PROFILE_ID_CH',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_SECURITY_CODE_CH',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_MIN_DE',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_MAX_DE',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_MIN_AT',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_MAX_AT',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_MIN_CH',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_MAX_CH',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_B2B_DE',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_RATEPAY_PRIVACY_URL_DE',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_RATEPAY_PRIVACY_URL_AT',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_RATEPAY_PRIVACY_URL_CH',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_ALLOWED',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_ZONE',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_ORDER_STATUS_ID',
            'MODULE_PAYMENT_RATEPAY_RECHNUNG_SORT_ORDER',
            'MODULE_PAYMENT_RATEPAY_SNIPPET_ID'
        );
    }
}
