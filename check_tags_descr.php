<?php
/*
Plugin Name: Check Tags Descr
Plugin URI: http://www.matriz.it/projects/check_tags_descr/
Description: It lists all tags that don't have description.
Version: 1.0
Author: Mattia Palugan
Author URI: http://www.matriz.it/
License: GPL2
*/

add_action('admin_menu','check_tags_descr_menu');
add_action('admin_head','check_tags_descr_javascript');
add_action('wp_ajax_check_tags_descr_save','check_tags_descr_save');

function check_tags_descr_menu() {
	add_submenu_page('edit.php','Check Tags Descr', 'Check Tags Descr', 'manage_categories', 'check_tags_descr', 'check_tags_descr_list');
}

function check_tags_descr_list() {
	if (!current_user_can('manage_categories'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	$tags = get_tags();
	$counter = count($tags);
	for($i=0;$i<$counter;$i++){
		if(trim($tags[$i]->description)!='' || $tags[$i]->taxonomy!='post_tag'){
			unset($tags[$i]);
		}
	}
	unset($i,$counter);
	$tags = array_merge($tags);
	$counter = count($tags);
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2>Check Tags Descr</h2>
		<table class="widefat tag fixed" cellspacing="0">
			<thead>
				<tr>
					<th class="manage-column column-name" scope="col"><?=__('Name');?></th>
					<th class="manage-column column-description" scope="col"><?=__('Description');?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="manage-column column-name" scope="col"><?=__('Name');?></th>
					<th class="manage-column column-description" scope="col"><?=__('Description');?></th>
				</tr>
			</tfoot>
			<tbody id="the-list" class="list:tag">
				<?php
				if($counter>0){
					for($i=0;$i<$counter;$i++){
						$name = apply_filters('term_name',$tags[$i]->name);
						?>
						<tr id="list_tag_row_<?=$tags[$i]->term_id;?>">
							<td class="name column-name"><strong><a href="edit-tags.php?action=edit&amp;taxonomy=post_tag&amp;tag_ID=<?=$tags[$i]->term_id;?>" title="<?=esc_attr(sprintf(__('Edit &#8220;%s&#8221;'),$name));?>" class="row-title"><?=$name;?></a></strong></td>
							<td class="description column-description">
								<form action="#" method="post" onsubmit="check_tags_descr_save(<?=$tags[$i]->term_id;?>,jQuery(this).find('textarea[name=description]')[0].value);return false;">
									<textarea name="description" rows="3" cols="40"></textarea>
									<input type="submit" class="button" name="submit" value="<?=esc_attr(__('Edit'));?>" />
								</form>
							</td>
						</tr>
						<?php
						unset($name);
					}
					unset($i);
				} else {
					echo '<tr><td colspan="2">'.__('No tags found!').'</td></tr>';
				}
				?>
			</tbody>
		</table>
	</div>
	<?php
}

function check_tags_descr_javascript() {
?>
<script type="text/javascript">
function check_tags_descr_save(tid,descr){
	jQuery.post(ajaxurl,{
		action: 'check_tags_descr_save',
		id: tid,
		descr: descr
	}, function(res){
		if(res==='1'){
			jQuery('#list_tag_row_'+tid).remove();
		} else {
			alert('<?=str_replace('\'','\\\'',__('Item not updated.'));?>');
		}
	});
}
</script>
<?php
}

function check_tags_descr_save() {
	$ok = false;
	$id = isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id']>0 ? (int)$_POST['id'] : 0;
	$descr = isset($_POST['descr']) && is_scalar($_POST['descr']) ? trim($_POST['descr']) : '';
	if($id>0 && $descr!=''){
		$tax = get_taxonomy('post_tag');
		if(!current_user_can($tax->cap->edit_terms)){
			wp_die(__('You are not allowed to edit this item.'));
		}
		unset($tax);
		$res = wp_update_term($id,'post_tag',array('description'=>$descr));
		if(is_array($res) && isset($res['term_id']) && $res['term_id']==$id){
			$ok = true;
		}
	}
	unset($id,$descr);
	echo $ok ? 1 : 0;
	unset($ok);
	die();
}
?>