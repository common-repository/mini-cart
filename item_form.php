<?php
require('wpframe.php');
wpframe_stop_direct_call(__FILE__);

$action = 'new';
if($_REQUEST['action'] == 'edit') $action = 'edit';

if(isset($_REQUEST['submit'])) {
	$status = ($_REQUEST['status']) ? 1 : 0;
	if($action == 'edit') { //Update goes here
		$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}minicart_item SET name=%s,description=%s,"
			. " price=%s, return_url=%s, type=%s, status=%s WHERE ID=%d",
			$_REQUEST['name'],$_REQUEST['content'], $_REQUEST['price'], $_REQUEST['return_url'], $_REQUEST['type'], $status, $_REQUEST['item']));
		
		wpframe_message('Item Saved', 'updated');		
	} else {
		$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}minicart_item(name,description,price,return_url,type,status,added_on) VALUES(%s,%s,%s,%s,%s,%s,NOW())",
			$_REQUEST['name'], $_REQUEST['content'], $_REQUEST['price'],$_REQUEST['return_url'], $_REQUEST['type'], $status));
		$_REQUEST['item'] = $item_id = $wpdb->insert_id;
		$action = 'edit';
		
		wpframe_message('Item Saved', 'updated');
	}
}

$item = array();
if($action == 'edit') {
	$item = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}minicart_item WHERE ID = $_REQUEST[item]");
}
?>

<div class="wrap">
<h2><?php printf(t("%s Item"), ucfirst($action)); ?></h2>

<?php if($action == 'edit') { ?>
<p><?php printf(t('To add the shopping cart for this item in your blog, insert the code [MINICART item="%d"] into any post.'), $_REQUEST['item']) ?></p>
<?php } ?>

<input type="hidden" id="title" name="ignore_me" value="This is here as a workaround for an editor bug" />

<?php
wpframe_add_editor_js();
?>
<script type="text/javascript" src="<?=$wpframe_plugin_folder?>/js/admin/item_form.js"></script>

<form name="post" action="" method="post" id="post">
<div id="poststuff">
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">

<div class="postbox">
<h3 class="hndle"><span><?php e('Item Name') ?></span></h3>
<div class="inside">
<input type='text' name='name' value='<?php echo stripslashes($item->name); ?>' />
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Description') ?></span></h3>
<div class="inside">
<?php the_editor(stripslashes($item->description)); ?>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Price') ?></span></h3>
<div class="inside">
<input type='text' name='price' value='<?php echo $item->price; ?>' /><br />
<?php e('All price is assumed to be in the currency specified in the Settings page.') ?>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Item Type') ?></span></h3>
<div class="inside">
<select name="type">
<option name="download" <?php if($item->type == 'download') print 'selected="selected"'; ?>>Download</option>
<option name="shipping" <?php if($item->type == 'shipping') print 'selected="selected"'; ?>>Shipping</option>
</select>

<p><?php e('If you set the type as shipping, the shipping address will be taken from the user.') ?></p>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Return URL') ?></span></h3>
<div class="inside">
<input type='text' name='return_url' value='<?php echo $item->return_url ?>' /><br />
<?php e('The thank you page the users should reach after the payment is made. If you are selling a downloadable item, you can put the download link here.') ?>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Status') ?></span></h3>
<div class="inside">
<label for="status"><?php e('Active') ?></label> <input type="checkbox" name="status" value="1" id="status" <?php if($item->status or $action=='new') print " checked='checked'"; ?> />
</div></div>


</div></div>
</div>

<p class="submit">
<?php wp_nonce_field('minicart_create_edit_item'); ?>
<input type="hidden" name="action" value="<?php echo $action; ?>" />
<input type="hidden" name="item" value="<?php echo $_REQUEST['item']; ?>" />
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<span id="autosave"></span>
<input type="submit" name="submit" value="<?php e('Save') ?>" style="font-weight: bold;" tabindex="4" />
</p>

</div>
</form>

</div>
