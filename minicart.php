<?php
/*
Plugin Name: MiniCart
Plugin URI: http://www.bin-co.com/tools/wordpress/plugins/mini-cart/
Description: MiniCart will impliment a mini-shopping-cart. The users will be able to buy one item at at time. You can embed the items into posts. This can also be used as a donation plugin.
Version: 1.00.1
Author: Binny V A
Author URI: http://binnyva.com/
*/

/**
 * Add a new menu under Manage to manage Polls
 */
add_action( 'admin_menu', 'minicart_add_menu_links' );
function minicart_add_menu_links() {
	global $wp_version;
	$view_level= 2;
	$page = 'edit.php';
	if($wp_version >= '2.7') $page = 'tools.php';
	
	add_submenu_page($page, __('Manage Cart Items', 'mini-cart'), __('Manage Cart Items', 'mini-cart'), $view_level, 'mini-cart/items.php');
	
	global $_registered_pages;
	$hookname = get_plugin_page_hookname("mini-cart/item_form.php", '' );
	$_registered_pages[$hookname] = true;
}

/// Add an option page for minicart.
add_action('admin_menu', 'minicart_option_page');
function minicart_option_page() {
	add_options_page(__('Mini-Cart Settings', 'mini-cart'), __('Mini-Cart Settings', 'mini-cart'), 8, basename(__FILE__), 'minicart_options');
}
function minicart_options() {
	if ( function_exists('current_user_can') && !current_user_can('manage_options') ) die(t('Cheatin&#8217; uh?'));
	if (! user_can_access_admin_page()) wp_die( t('You do not have sufficient permissions to access this page.') );

	require(ABSPATH. '/wp-content/plugins/mini-cart/options.php');
}

add_shortcode( 'MINICART', 'minicart_shortcode' );
function minicart_shortcode( $attr ) {
	$item = 0;
	$reason = '';
	$comment = '';
	
	if(isset($attr['item'])) {
		$item = $attr['item'];
	} else if(isset($attr[0])) {
		$item = $attr[0];
		if(isset($attr[1])) $reason = $attr[1];
		if(isset($attr[2])) $comment = $attr[2];
	}
	
	if(isset($attr['reason'])) $reason = $attr['reason'];
	if(isset($attr['comment'])) $comment = $attr['comment'];
	
	if($item !== 0 and !is_numeric($item)) {
		if(count($attr) == 1) $reason = $item;
		$item = 0; // Invalid item id defaults to donation model.
	}
	
	return minicart_show_widget($item, $reason, $comment, false);
}

function minicart_show_widget($item_id=0, $reason='', $comment='', $widget=true) {
	$data = '';
	$amount = 5;
	$item = array();
	
	global $wpdb;
	if($item_id) $item = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}minicart_item WHERE ID=$item_id");
	
	if($item) { // Buy an item.
		$item_name = $name = stripslashes($item->name);
		$widget_title = __('Buy', 'mini-cart') . ' ' . $name;
		$return_page = ($item->return_url) ? $item->return_url : 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		
	} else { // Donate
		$item_name = __('Donate', 'mini-cart') . ' ' . $reason;
		$widget_title = __('Donate', 'mini-cart');
		$return_page = get_option('minicart_donate_return_page');
		if(!$return_url) $return_page = 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	}
	
	if($widget) $data .= "<li><h3>" . $widget_title . "</h3>";
	
	$accout_email = get_option('minicart_account_email');
	$description = stripslashes($item->description);
	$notify_url = $GLOBALS['wpframe_wordpress'] . "/wp-content/plugins/mini-cart/ipn.php?item=$item_id&name=". urlencode($item_name) . "&reason=" . urlencode($reason) . "&comment=" . urlencode($comment);
	$currency = get_option('minicart_currency');
	$donate_text = get_option('minicart_donate_text');
	
	$data .= <<<END
<div id='minicart-widget'>
<h3>$name</h3>
<p>$description</p>

<form name="minicart" action="https://www.paypal.com/cgi-bin/webscr" method="post" style="text-align:left;">
<input type="hidden" name="cmd" value="_xclick" />
<input type="hidden" name="business" value="$accout_email" />
<input type="hidden" name="return" value="$return_page" />
<input type="hidden" name="notify_url" id="notify_url" value="$notify_url" />
<input type="hidden" name="item_name" value="$item_name" />
<input type="hidden" name="item_number " value="$item_id" />
<input type="hidden" name="currency_code" value="$currency" />
<input type="hidden" name="no_shipping" value="1" />
END;

	if(!$item_id) {
		//Donate part...
		$donation = get_option('minicart_donate_text');
		$donate_text_action = __('Donate', 'mini-cart');
		
		$data .= <<<END2
<label for="amount">$donate_text</label>
<input type="text" id="amount" name="amount" value="$amount" size="4" />$currency<br />
<input type="submit" name="action" value="$donate_text_action" />
END2;
	} else {
		if($item->type == 'shipping') {
			$data .= '<label for="os0">' . __('Shipping Address', 'mini-cart') . '</label><br /><textarea id="os0" name="os0" rows="5" cols="25"></textarea><br />';
			
		} else { //Downloadable don't need the shipping address.
			$data .= '<input type="hidden" name="os0" value="" />';
		}
	
		$checkout_text = __('Checkout', 'mini-cart');
		$email_text = __('Email', 'mini-cart');
		$price_text = __('Price', 'mini-cart');
		
		$data .= <<<END3
<input type="hidden" name="on0" value="Shipping Address" />

<input type="hidden" name="on1" value="Email" />
<label for="os1">$email_text</label><br />
<input type="text" id="os1" name="os1" maxlength="60" /><br />

<label for="amount">$price_text: {$item->price} $currency</label><br />
<input type="hidden" id="amount" name="amount" value="{$item->price}"  />
<input type="submit" name="action" value="$checkout_text" />
END3;
	}
	
	$data .= '</form></div>';

	if($widget) $data .= "</li>";
	if($widget) print $data;
	else return $data;
}

function minicart_widget_init() {
	if (! function_exists("register_sidebar_widget")) return;
	
	register_sidebar_widget(__('Show Donate','mini-cart'), 'minicart_show_widget');
}
add_action('plugins_loaded', 'minicart_widget_init');


/**
 * Stuff to do when the plugin is activated - create the tables, stuff like that.
 */
add_action('activate_mini-cart/minicart.php','minicart_activate');
function minicart_activate() {
	global $wpdb, $wpframe,$current_user;
	
    get_currentuserinfo();
    
	// Initial options.
	add_option('minicart_admin_email', $current_user->user_email);
	add_option('minicart_account_email', $current_user->user_email);
	add_option('minicart_currency', 'USD');
	add_option('minicart_return_page', '');
	add_option('minicart_default_donate_amount', 5);
	add_option('minicart_donate_text', 'Amount');
	
	$database_version = '1';
	$installed_db = get_option('minicart_db_version');
	
	if($database_version != $installed_db) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		// Create the table structure
		$sql = "CREATE TABLE {$wpdb->prefix}minicart_item (
					ID int(11) unsigned NOT NULL auto_increment,
					name varchar(200) NOT NULL,
					post_url varchar(200) NOT NULL,
					return_url varchar(255) NOT NULL,
					price float NOT NULL,
					description text NOT NULL,
					summary mediumtext NOT NULL,
					added_on datetime NOT NULL,
					type enum('download','shipping','other') NOT NULL default 'other',
					status enum('1','0') NOT NULL default '1',
					PRIMARY KEY  (ID)
				) ;
				
				CREATE TABLE {$wpdb->prefix}minicart_purchase (
					ID int(11) unsigned NOT NULL auto_increment,
					item_ID int(11) NOT NULL,
					reason varchar(255) NOT NULL,
					comment varchar(255) NOT NULL,
					payment_made_on datetime NOT NULL,
					payer_id varchar(50) NOT NULL,
					payer_name varchar(100) NOT NULL,
					payer_address varchar(255) NOT NULL,
					payer_email varchar(100) NOT NULL,
					PRIMARY KEY  (ID)
				) ;";
		dbDelta($sql);
		update_option( "minicart_db_version", $database_version );
	}
}
