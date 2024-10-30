<?php
include('wpframe.php');

if(isset($_REQUEST['submit']) and $_REQUEST['submit']) {
	$options = array('admin_email', 'account_email', 'currency', 'donate_return_page', 'default_donate_amount', 'donate_text');
	foreach($options as $opt) {
		if(isset($_POST[$opt])) update_option('minicart_' . $opt, $_POST[$opt]);
		else update_option('minicart_' . $opt, 0);
	}
	wpframe_message(t("Options updated"));
}
?>
<div class="wrap">
<h2>ECommerce Settings</h2>

<form name="post" action="" method="post" id="post">
<div id="poststuff">
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">

<?php
showEntryOption('admin_email', 'Admin Email', 'An email will be sent to this address when a payment has been made');
showEntryOption('account_email', 'Paypal Account Email');
showEntryOption('donate_return_page', 'Return URL for donations', 'If left empty, the user will return to the post which has the donation form. Usually, this points to a thank you page.');
showEntryOption('default_donate_amount', 'Default Donate Amount');
showEntryOption('donate_text', 'Text next to donate field');
?>

<div class="postbox">
<h3 class="hndle"><span><?php e('Prefered Currency') ?></span></h3>
<div class="inside">
<select name="currency" id="currency">
<option value=""></option>
<?php
	$currencies = array(
	'USD' => 'U.S. Dollar (USD)',
	'AUD' => 'Australian Dollar (AUD)',
	'CAD' => 'Canadian Dollar (CAD)',
	'CZK' => 'Czech Koruna (CZK)',
	'DKK' => 'Danish Krone (DKK)',
	'EUR' => 'Euro (EUR)',
	'HKD' => 'Hong Kong Dollar (HKD)',
	'HUF' => 'Hungarian Forint (HUF)',
	'NZD' => 'New Zealand Dollar (NZD)',
	'NOK' => 'Norwegian Krone (NOK)',
	'PLN' => 'Polish Zloty (PLN)',
	'GBP' => 'Pound Sterling (GBP)',
	'SGD' => 'Singapore Dollar (SGD)',
	'SEK' => 'Swedish Krona (SEK)',
	'CHF' => 'Swiss Franc (CHF)',
	'JPY' => 'Yen (JPY)'
);
foreach($currencies as $id=>$name) {
	$selected = '';
	if(get_option('minicart_currency') == $id) $selected = ' selected="selected"';
	print "	<option value='$id'$selected>$name</option>\n";
}
?>
</select>
</div></div>

<p class="submit">
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<span id="autosave"></span>
<input type="submit" name="submit" value="<?php e('Save Options') ?>" style="font-weight: bold;" />
</p>

</div>
</div>
</form>

</div>

<?php function showEntryOption($option, $title, $info="") { ?>
<div class="postbox">
<h3 class="hndle"><span><?php e($title) ?></span></h3>
<div class="inside">
<input type="text" name="<?=$option?>"  id="<?=$option?>" value="<?php echo get_option('minicart_'.$option); ?>" /><br />
<?php if($info) { ?><p><?php echo $info ?></p><?php } ?>
</div></div>
<?php
}
