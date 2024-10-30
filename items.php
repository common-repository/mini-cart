<?php
include('wpframe.php');
wpframe_stop_direct_call(__FILE__);

if($_REQUEST['action'] == 'delete') {
	$wpdb->get_results("DELETE FROM {$wpdb->prefix}minicart_item WHERE ID='$_REQUEST[item]'");
	wpframe_message('Item Deleted', 'updated');
}
?>

<div class="wrap">
<h2><?php e("Manage Items"); ?></h2>

<?php
wp_enqueue_script( 'listman' );
wp_print_scripts();
?>

<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><div style="text-align: center;"><?php e("#") ?></div></th>
		<th scope="col"><?php e("Title") ?></th>
		<th scope="col"><?php e("Price") ?></th>
		<th scope="col"><?php e("Added On") ?></th>
		<th scope="col"><?php e("Status") ?></th>
		<th scope="col" colspan="3"><?php e("Action") ?></th>
	</tr>
	</thead>

	<tbody id="the-list">
<?php
// Retrieve the itemes
$all_item = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}minicart_item`");

if (count($all_item)) {
	$status = array(t('Inactive'), t('Active'));
	$count = 0;
	foreach($all_item as $item) {
		$count++;
		$class = ('alternate' == $class) ? '' : 'alternate';
		print "<tr id='item-{$item->ID}' class='$class'>\n";
		?>
		<th scope="row" style="text-align: center;"><?php echo $count ?></th>
		<td><?php echo $item->name ?></td>
		<td><?php echo $item->price ?></td>
		<td><?php echo ($item->added_on == '0000-00-00') ? '' : date(get_option('date_format'), strtotime($item->added_on)) ?></td>
		<td><?php echo $status[$item->status] ?></a></td>
		<td><a href='edit.php?page=mini-cart/item_form.php&amp;item=<?=$item->ID?>&amp;action=edit' class='edit'><?= t('Edit'); ?></a></td>
		<td><a href='edit.php?page=mini-cart/items.php&amp;action=delete&amp;item=<?=$item->ID?>' class='delete' onclick="return confirm('<?=addslashes(sprintf(t("You are about to delete '%s'. This will delete all the attendees within this item. Press 'OK' to delete and 'Cancel' to stop."), $item->name))?>');"><?=t('Delete')?></a></td>
		</tr>
<?php
		}
	} else {
?>
	<tr>
		<td colspan="7"><?php e('No items found.') ?></td>
	</tr>
<?php
}
?>
	</tbody>
</table>

<a href="edit.php?page=mini-cart/item_form.php&amp;action=new"><?php e("Create New item") ?></a>
</div>
