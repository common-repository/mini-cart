<?php
require('../../../wp-blog-header.php');

// read the post from PayPal system and add 'cmd'  
$req = 'cmd=_notify-validate';  
foreach ($_POST as $key => $value) {  
	$value = urlencode(stripslashes($value));  
	$req .= "&$key=$value";  
}

// post back to PayPal system to validate  
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";  
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";  
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";  
  
$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);  
if ($fp) {
	fputs ($fp, $header . $req);
	while (!feof($fp)) {
		$res = fgets ($fp, 1024);
		if (strcmp ($res, "VERIFIED") == 0) {
			$home = get_option('home');
			$full = print_r($_REQUEST, 1);
			$details = sprintf("Hi,

A payment was made at %s ...

Item Name: %s
Payer Name: %s
Address: %s
Email: %s

Please see your PayPal page for more datails about the payment.

--
Mini-Cart WordPress Plugin", $home, $_REQUEST['name'], $_REQUEST['first_name'] . ' ' . $_REQUEST['last_name'], $_REQUEST['option_selection1'], $_REQUEST['option_selection2']);

			if($_REQUEST['payment_status'] == 'Completed') {
				mail(get_option('minicart_admin_email'), "Payment for $_REQUEST[name] Made", $details);
				$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}minicart_purchase
					(item_ID, reason, comment, payer_id, payer_name, payer_address, payer_email, payment_made_on) VALUES
					(%d, 	  %s,	  %s,	   %s,		 %s,		 %s,			%s,			 NOW())",
					$_REQUEST['item'], $_REQUEST['reason'], $_REQUEST['comment'], $_REQUEST['payer_email'], $_REQUEST['first_name'] . ' ' . $_REQUEST['last_name'], $_REQUEST['option_selection1'], $_REQUEST['option_selection2']));
			}
		}
		
		else if (strcmp ($res, "INVALID") == 0) {
			// PAYMENT INVALID & INVESTIGATE MANUALY!  
		}
	}
	fclose ($fp);
}

// file_put_contents('Debug.txt', $res . "\n" . 
// 	print_r($_REQUEST, 1)
// 	. $wpdb->prepare("INSERT INTO {$wpdb->prefix}minicart_purchase(item_ID, reason, comment, payer_id, payer_name, payer_address, payer_email, payment_made_on) VALUES(%d, %s, %s, %s, %s, %s, NOW())",
// 					$_REQUEST['item'], $_REQUEST['reason'], $_REQUEST['comment'], $_REQUEST['payer_email'], $_REQUEST['first_name'] . ' ' . $_REQUEST['last_name'], $_REQUEST['option_selection1'], $_REQUEST['option_selection2']));
