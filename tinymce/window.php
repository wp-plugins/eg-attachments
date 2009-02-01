<?php

	if (!function_exists('bloginfo')) {

		$root = str_replace('\\','/',dirname(dirname(dirname(dirname(dirname(__FILE__))))) ). '/' ;

		if (file_exists( $root . 'wp-load.php') )
			require_once( $root . 'wp-load.php');
		elseif (file_exists( 'wp-load.php') )
			require_once( 'wp-load.php');
		elseif (file_exists( $root . 'wp-config.php') )
			require_once( $root . 'wp-config.php');
		elseif (file_exists( 'wp-config.php') )
			require_once( 'wp-config.php');
		else exit("Could not find wp-load.php or wp-config.php");
	}

// check for rights
/*
if ( !is_user_logged_in() || !current_user_can('edit_posts') ) 
	wp_die(__("You are not allowed to be here"));

global $wpdb;
*/

define('EG_ATTACHMENT_DOMAIN',  'eg-attachments');

if (isset($_GET['post_id'])) {
	$attachment_list = get_children('post_type=attachment&post_parent='.$_GET['post_id']);
	
	$attachment_string = __('No attachment available for this post', EG_ATTACHMENT_DOMAIN);
	if ($attachment_list) {
		$attachment_string = '<select id="doclist" name="doclist" size="5" multiple>';
		foreach ($attachment_list as $key => $attachment) {
			$attachment_string .= '<option id="doclist" value="'.$attachment->ID.'">'.$attachment->post_title.'</option>';
		}
		$attachment_string .= '</select>';
	}
}
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<title>EG Attachments</title>	
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>	
	<script language="javascript" type="text/javascript" src="eg-attachments.js"></script>
</head>
<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';">
	<form name="EGAttachments" action="#">
		<table border="0">
			<tr>
				<td><label for="orderby"><?php _e('Order by: ',EG_ATTACHMENT_DOMAIN); ?></label></td>
				<td>
					<select id="orderby" name="orderby">
						<option value="0"><?php _e('Title', EG_ATTACHMENT_DOMAIN); ?></option>
						<option value="ID"><?php _e('ID', EG_ATTACHMENT_DOMAIN); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="sortorder"><?php _e('Sort Order: ',EG_ATTACHMENT_DOMAIN); ?></label></td>
				<td>
					<select id="sortorder" name="sortorder">
						<option value="0"><?php _e('Ascending', EG_ATTACHMENT_DOMAIN); ?></option>
						<option value="DESC"><?php _e('Descending', EG_ATTACHMENT_DOMAIN); ?></option>
					</select>
				</td>
			</tr>			
			<tr>
				<td><label for="listsize"><?php _e('List size: ',EG_ATTACHMENT_DOMAIN); ?></label></td>
				<td>
					<select id="listsize" name="listsize">
						<option value="0">Large</option>
						<option value="medium">Medium</option>
						<option value="small">Small</option>
					</select>
				</td>
			</tr>
			<tr>
				<td><label for="doctype"><?php _e('Document type: ',EG_ATTACHMENT_DOMAIN); ?></label></td>
				<td>
					<select id="doctype" name="doctype">
						<option value="0">Document</option>
						<option value="image">Image</option>						
					</select>
				</td>
			</tr>			
			<tr>
				<td valign="top"><label for="doclist"><?php _e('Document list: ',EG_ATTACHMENT_DOMAIN); ?></label></td>
				<td>
					<?php echo $attachment_string; ?>
				</td>
			</tr>
			<tr>
				<td><label for="title"><?php _e('Title: ',EG_ATTACHMENT_DOMAIN); ?></label></td>
				<td><input id="title" name="title" type="text" value="" /></td>
			</tr>
			<tr>
				<td><label for="titletag"><?php _e('Title tag: ',EG_ATTACHMENT_DOMAIN); ?></label></td>
				<td><input id="titletag" name="titletag" type="text" value="h2" /></td>
			</tr>
		</table>
	<div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", EG_ATTACHMENT_DOMAIN); ?>" onclick="tinyMCEPopup.close();" />
		</div>

		<div style="float: right">
			<input type="submit" id="insert" name="insert" value="<?php _e("Insert", EG_ATTACHMENT_DOMAIN); ?>" onclick="insertEGAttachmentsShortCode();" />
		</div>
	</div>	
	</form>	
</body>
</html>
