<?php

/* -----------------------------------------------------------------------------------------

   $Id: print_order.php 1166 2005-08-21 00:52:02Z mz $



   XT-Commerce - community made shopping

   http://www.xt-commerce.com



   Copyright (c) 2003 XT-Commerce

   -----------------------------------------------------------------------------------------

   based on:

   (c) 2003	 nextcommerce (print_order.php,v 1.1 2003/08/19); www.nextcommerce.org



   Released under the GNU General Public License

   ---------------------------------------------------------------------------------------*/



  require('includes/application_top.php');

  // include needed functions

  require_once(DIR_FS_INC .'xtc_get_order_data.inc.php');

  require_once(DIR_FS_INC .'xtc_get_attributes_model.inc.php');

  require_once(DIR_FS_INC .'xtc_not_null.inc.php');

  require_once(DIR_FS_INC .'xtc_format_price_order.inc.php');



  $smarty = new Smarty;



  $order_query_check = xtc_db_query("SELECT

  					customers_id

  					FROM ".TABLE_ORDERS."

  					WHERE orders_id='".(int)$_GET['oID']."'");



  $order_check = xtc_db_fetch_array($order_query_check);

 // if ($_SESSION['customer_id'] == $order_check['customers_id'])

  //	{

  	// get order data



  	include(DIR_WS_CLASSES . 'order.php');

  	$order = new order($_GET['oID']);





  	$smarty->assign('address_label_customer',xtc_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />'));

  	$smarty->assign('address_label_shipping',xtc_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'));

  	$smarty->assign('address_label_payment',xtc_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />'));

  	$smarty->assign('csID',$order->customer['csID']);

  	// get products data

        $order_query=xtc_db_query("SELECT

        				products_id,

        				orders_products_id,

        				products_model,

        				products_name,

        				final_price,

        				products_quantity

        				FROM ".TABLE_ORDERS_PRODUCTS."

        				WHERE orders_id='".(int)$_GET['oID']."'");

        $order_data=array();

        while ($order_data_values = xtc_db_fetch_array($order_query)) {

        	$attributes_query=xtc_db_query("SELECT

        				products_options,

        				products_options_values,

        				price_prefix,

        				options_values_price

        				FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES."

        				WHERE orders_products_id='".$order_data_values['orders_products_id']."'");

        	$attributes_data='';

        	$attributes_model='';

        	while ($attributes_data_values = xtc_db_fetch_array($attributes_query)) {

        	$attributes_data .='<br />'.$attributes_data_values['products_options'].':'.$attributes_data_values['products_options_values'];

        	$attributes_model .='<br />'.xtc_get_attributes_model($order_data_values['products_id'],$attributes_data_values['products_options_values'],$attributes_data_values['products_options']);

        	}

        $order_data[]=array(

        		'PRODUCTS_MODEL' => $order_data_values['products_model'],

        		'PRODUCTS_NAME' => $order_data_values['products_name'],

        		'PRODUCTS_ATTRIBUTES' => $attributes_data,

        		'PRODUCTS_ATTRIBUTES_MODEL' => $attributes_model,

        		'PRODUCTS_PRICE' => xtc_format_price_order($order_data_values['final_price'],1,$order->info['currency']),

        		'PRODUCTS_QTY' => $order_data_values['products_quantity']);

        }

  	// get order_total data

  	$oder_total_query=xtc_db_query("SELECT

                      title,

                      text,

                      class,

                      value,

                      sort_order

  					FROM ".TABLE_ORDERS_TOTAL."

  					WHERE orders_id='".$_GET['oID']."'

  					ORDER BY sort_order ASC");



  	$order_total=array();

  	while ($oder_total_values = xtc_db_fetch_array($oder_total_query)) {



  	$order_total[]=array(

              'TITLE' => $oder_total_values['title'],

              'CLASS'=> $oder_total_values['class'],

              'VALUE'=> $oder_total_values['value'],

              'TEXT' => $oder_total_values['text']);

    if ($oder_total_values['class']='ot_total') $total=$oder_total_values['value'];

  	}



  	// assign language to template for caching

  	$smarty->assign('language', $_SESSION['language']);

    $smarty->assign('logo_path',HTTP_SERVER  . DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');

	$smarty->assign('oID',$_GET['oID']);

	if ($order->info['payment_method']!='' && $order->info['payment_method']!='no_payment') {

	include(DIR_FS_CATALOG.'lang/'.$_SESSION['language'].'/modules/payment/'.$order->info['payment_method'].'.php');

 	$payment_method=constant(strtoupper('MODULE_PAYMENT_'.$order->info['payment_method'].'_TEXT_TITLE'));

  	$smarty->assign('PAYMENT_METHOD',$payment_method);

    }

    $smarty->assign('COMMENTS', $order->info['comments']);

  	$smarty->assign('DATE',xtc_date_short($order->info['date_purchased']));

  	$smarty->assign('order_data', $order_data);

  	$smarty->assign('order_total', $order_total);

  	// Get the RatePAY stuff for the Invoice
	$sql = "select descriptor from pi_ratepay_rechnung_orders where order_number = '".xtc_db_input($_GET['oID'])."'";
	$query = xtc_db_query($sql);
	$descriptorArray = xtc_db_fetch_array($query);

	require_once '../includes/modules/payment/pi_ratepay_rechnung.php';

	$piRatepay = new pi_ratepay_rechnung();

	$accountHolder = $piRatepay->merchantName;
	$bank = $piRatepay->bankName;
	$sortCode = $piRatepay->sortCode;
	$accountNr = $piRatepay->accountNr;
	$descriptor = $descriptorArray['descriptor'];
	$iban = $piRatepay->iban;
	$swift = $piRatepay->swift;
	$email = $piRatepay->email;
	$extraField = $piRatepay->extraField;

	$owner = $piRatepay->owner;
	$hr = $piRatepay->hr;
	$court = $piRatepay->court;
	$fon = $piRatepay->fon;
	$fax = $piRatepay->fax;
	$street = $piRatepay->street;
	$plz  = $piRatepay->plz;


	$sql = "select configuration_value from configuration where configuration_key = 'STORE_OWNER_VAT_ID'";
	$query = xtc_db_query($sql);
	$ustArray = xtc_db_fetch_array($query);
	$ust = $ustArray['configuration_value'];

	$sql = "select configuration_value from configuration where configuration_key = 'STORE_OWNER_EMAIL_ADDRESS'";
	$query = xtc_db_query($sql);
	$mailArray = xtc_db_fetch_array($query);
	$mail = $mailArray['configuration_value'];

	$smarty->assign('accountHolder', $accountHolder);
	$smarty->assign('bank', $bank);
	$smarty->assign('sortCode', $sortCode);
	$smarty->assign('accountNr', $accountNr);
	$smarty->assign('descriptor', $descriptor);
	$smarty->assign('iban', $iban);
	$smarty->assign('swift', $swift);
	$smarty->assign('email', $mail);
	$smarty->assign('extraField', $extraField);
	$smarty->assign('url', $_SERVER['SERVER_NAME']);
	$smarty->assign('ust', $ust);
	$smarty->assign('owner', $owner);

	$smarty->assign('address_footer',$street . " , " . $plz);


	$smarty->assign('owner', $owner);
	$smarty->assign('fon', $fon);
	$smarty->assign('fax', $fax);
	$smarty->assign('court', $court);
	$smarty->assign('hr', $hr);

	$smarty->assign('ownerText', PI_RATEPAY_RECHNUNG_PDF_OWNER);
	$smarty->assign('fonText', PI_RATEPAY_RECHNUNG_PDF_FON);
	$smarty->assign('faxText', PI_RATEPAY_RECHNUNG_PDF_FAX);
	$smarty->assign('emailText', PI_RATEPAY_RECHNUNG_PDF_EMAIL);
	$smarty->assign('courtText', PI_RATEPAY_RECHNUNG_PDF_COURT);
	$smarty->assign('hrText', PI_RATEPAY_RECHNUNG_PDF_HR);
	$smarty->assign('ustText', PI_RATEPAY_RECHNUNG_PDF_UST);
	$smarty->assign('bullText', PI_RATEPAY_RECHNUNG_PDF_BULL);
	$smarty->assign('accountholderText', PI_RATEPAY_RECHNUNG_PDF_ACCOUNTHOLDER);
	$smarty->assign('banknameText', PI_RATEPAY_RECHNUNG_PDF_BANKNAME);
	$smarty->assign('bankcodenumberText', PI_RATEPAY_RECHNUNG_PDF_BANKCODENUMBER);
	$smarty->assign('accountnumberText', PI_RATEPAY_RECHNUNG_PDF_ACCOUNTNUMBER);
	$smarty->assign('swiftbicText', PI_RATEPAY_RECHNUNG_PDF_SWIFTBIC);
	$smarty->assign('ibanText', PI_RATEPAY_RECHNUNG_PDF_IBAN);
	$smarty->assign('intdescText', PI_RATEPAY_RECHNUNG_PDF_INTERNATIONALDESC);
	$smarty->assign('paytransferText', PI_RATEPAY_RECHNUNG_PDF_PAYTRANSFER);
	$smarty->assign('payuntilText', PI_RATEPAY_RECHNUNG_PDF_PAYUNTIL);
	$smarty->assign('referenceText', PI_RATEPAY_RECHNUNG_PDF_REFERENCE);
	$smarty->assign('additionalText1', PI_RATEPAY_RECHNUNG_PDF_ADDITIONALINFO_1);
	$smarty->assign('additionalText2', PI_RATEPAY_RECHNUNG_PDF_ADDITIONALINFO_2);
	$smarty->assign('additionalText3', PI_RATEPAY_RECHNUNG_PDF_ADDITIONALINFO_3);
	$smarty->assign('additionalText4', PI_RATEPAY_RECHNUNG_PDF_ADDITIONALINFO_4);
	$smarty->assign('abovearticleText', PI_RATEPAY_RECHNUNG_PDF_ABOVEARTICLE);
	$smarty->assign('descriptorText', PI_RATEPAY_RECHNUNG_PDF_DESCRIPTOR);
	// End RatePAY stuff

  	// dont allow cache

  	$smarty->caching = false;



	$smarty->template_dir=DIR_FS_CATALOG.'templates';

	$smarty->compile_dir=DIR_FS_CATALOG.'templates_c';

	$smarty->config_dir=DIR_FS_CATALOG.'lang';



  	$smarty->display(CURRENT_TEMPLATE . '/admin/pi_ratepay_rechnung_print_order.html');

//	} else {



//  	$smarty->display(CURRENT_TEMPLATE . '/error_message.html');

//	}



?>