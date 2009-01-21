<?php
if (!defined('WP_CONTENT_URL')) die;

add_action('admin_menu', 'ep_admin_menu');
add_action('admin_head', 'ep_meta_box');
add_action('edit_post', 'ep_post_save');
add_action('publish_post', 'ep_post_save');
add_action('save_post', 'ep_post_save');

function ep_meta_box() {
	add_meta_box('ep_add_form_field', 'External URL', 'ep_add_form_field', 'post', 'normal', 'low');
}

function ep_add_form_field(){
	global $post;
	
	if (isset($_POST['ep_url'])) {
		$url = clean_url($_POST['ep_url']);
	} else if (!empty($post)) {
		$url = clean_url(get_post_meta($post->ID, '_external_permalink', true));
	} else {
		$url = '';
	}
	
	?>
	<input type="text" name="ep_url" style="width:99%;" value="<?php echo $url;?>" />
	<input id="ep_nonce" name="ep_nonce" type="hidden" value="<?php echo wp_create_nonce(plugin_basename(__FILE__)); ?>" />
	<?php
}
	
function ep_post_save($post_id=false){
	if (!wp_verify_nonce($_POST['ep_nonce'], plugin_basename(__FILE__))) {
		return $post_id;
	}
	if (!isset($_POST['ep_url'])) {
		return $post_id;
	}
	if (!$post_id) {
		$post_id = (int) $_POST['post_ID'];
		if (!$post_id) {
			return 0;
		}
	}
	
	if (add_post_meta($post_id, '_external_permalink', $_POST['ep_url'], true) === false) {
		update_post_meta($post_id, '_external_permalink', $_POST['ep_url']);
	}
}
		
function ep_admin_menu() {
	add_submenu_page('options-general.php', 'External Permalinks', 'External Permalinks', 8, 'External Permalinks', 'ep_submenu');
}

function ep_submenu() {
	global $ep_options;

	$ep_options = get_option('ep_options');
	
	if (!empty($_POST['ep_save'])) {
		check_admin_referer('external-permalinks');
		
		$ep_options['notice_text']     		   = strip_tags($_POST['notice_text']);
		$ep_options['show_notice_in_the_loop'] = (int) $_POST['show_notice_in_the_loop'];
		$ep_options['show_notice_in_rss_feed'] = (int) $_POST['show_notice_in_rss_feed'];
		$ep_options['redirect_comments_page']  = (int) $_POST['redirect_comments_page'];
		$ep_options['show_excerpt']			   = (int) $_POST['show_excerpt'];
		
		update_option('ep_options', $ep_options);
			
		echo '<div id="message" class="updated fade"><p>' . __('Settings saved successfully.', 'external_permalinks') . '</p></div>' . "\n";		
	}
	
	?>
	<div class="wrap">
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
	<h2><?php _e('External Permalinks Options', 'external_permalinks'); ?></h2>
	<div class="updated"><p><?php _e('To link to an external post, fill in the "External Permalink" form field in your add/edit post form.'); ?></p></div>
	<table class="form-table">
	<tr>
	<th scope="row" valign="top"><?php _e('Notice Text', 'external_permalinks'); ?></th>
	<td>
	<input type="text" name="notice_text" size="30" value="<?php echo htmlspecialchars($ep_options['notice_text']); ?>" />
	</td>
	</tr>
	<tr>
	<th scope="row" valign="top"><?php _e('Show notice in the loop', 'external_permalinks'); ?></th>
	<td>
	<select name="show_notice_in_the_loop">
	<option value="1" <?php if ($ep_options['show_notice_in_the_loop'] == 1): ?>selected="selected"<?php endif; ?>><?php _e('Yes', 'external_permalinks'); ?></option>
	<option value="0" <?php if ($ep_options['show_notice_in_the_loop'] == 0): ?>selected="selected"<?php endif; ?>><?php _e('No', 'external_permalinks'); ?></option>
	</select>
	</td>
	</tr>
	<tr>
	<th scope="row" valign="top"><?php _e('Show notice in RSS feed', 'external_permalinks'); ?></th>
	<td>
	<select name="show_notice_in_rss_feed">
	<option value="1" <?php if ($ep_options['show_notice_in_rss_feed'] == 1): ?>selected="selected"<?php endif; ?>><?php _e('Yes', 'external_permalinks'); ?></option>
	<option value="0" <?php if ($ep_options['show_notice_in_rss_feed'] == 0): ?>selected="selected"<?php endif; ?>><?php _e('No', 'external_permalinks'); ?></option>
	</select>
	</td>
	</tr>
	<tr>
	<th scope="row" valign="top"><?php _e('Redirect comments page', 'external_permalinks'); ?></th>
	<td>
	<select name="redirect_comments_page">
	<option value="1" <?php if ($ep_options['redirect_comments_page'] == 1): ?>selected="selected"<?php endif; ?>><?php _e('Yes', 'external_permalinks'); ?></option>
	<option value="0" <?php if ($ep_options['redirect_comments_page'] == 0): ?>selected="selected"<?php endif; ?>><?php _e('No', 'external_permalinks'); ?></option>
	</select> <?php _e('Note: This will only work if your theme uses the default comments_popup_link() method.', 'external_permalinks'); ?>
	</td>
	</tr>
	</tr>
	<tr>
	<th scope="row" valign="top"><?php _e('Show excerpt', 'external_permalinks'); ?></th>
	<td>
	<select name="show_excerpt">
	<option value="0" <?php if ($ep_options['show_excerpt'] == 0): ?>selected="selected"<?php endif; ?>><?php _e('When available', 'external_permalinks'); ?></option>
	<option value="1" <?php if ($ep_options['show_excerpt'] == 1): ?>selected="selected"<?php endif; ?>><?php _e('Always', 'external_permalinks'); ?></option>
	</select>
	</td>
	</tr>
	<tr>
	<td colspan="2">
	<?php wp_nonce_field('external-permalinks'); ?>
	<span class="submit"><input name="ep_save" value="<?php _e('Save Changes', 'external_permalinks'); ?>" type="submit" class="button-primary"  /></span>
	</td>
	</tr>	
	</table>	
	</form>
	<h3><?php _e('Acknowledgements', 'external_permalinks'); ?></h3>	
	<div>
	<p>
		Subscribe to my blog at <a href="http://www.improvingtheweb.com" target="_blank">Improving The Web</a> : <a href="http://rss.improvingtheweb.com/improvingtheweb/wVZp" target="_blank">RSS</a> | <a href="http://twitter.com/improvingtheweb" target="_blank">Twitter</a> 
		(I've got alot more plugins coming!)
	</p>	
	</div>
	<?php
}
?>