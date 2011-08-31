<?php

require_once( dirname( dirname(__FILE__) ) .'/eg-attachments-bootstrap.php');
//Check for rights
if ( !is_user_logged_in() || !current_user_can('edit_posts') )
	wp_die(__("You are not allowed to access this file."));

if ( ! defined('EG_ATTACH_OPTIONS_ENTRY') ) {
	require_once( dirname( dirname( __FILE__) ) . '/eg-attachments-config.inc.php');
}

$eg_attach_options = get_option(EG_ATTACH_OPTIONS_ENTRY);

if (!$eg_attach_options['shortcode_auto_default_opts']) {
	$default_values = $EG_ATTACHMENT_SHORTCODE_DEFAULTS;
	list($default_values['orderby'], $default_values['sortorder']) = explode(' ', $EG_ATTACHMENT_SHORTCODE_DEFAULTS['orderby'] );
	$default_values['force_saveas'] = $eg_attach_options['force_saveas'];
	$default_values['logged_users'] = $eg_attach_options['logged_users_only'];
}
else {
	$default_values = array(
		'orderby'  		=> $eg_attach_options['shortcode_auto_orderby'],
		'order'			=> $eg_attach_options['shortcode_auto_order'],
		'size'     		=> $eg_attach_options['shortcode_auto_size'],
		'doctype'  		=> $eg_attach_options['shortcode_auto_doc_type'],
		'docid'    		=> 0,
		'title'    		=> $eg_attach_options['shortcode_auto_title'],
		'titletag' 		=> $eg_attach_options['shortcode_auto_title_tag'],
		'label'    		=> $eg_attach_options['shortcode_auto_label'],
		'force_saveas'	=> $eg_attach_options['force_saveas'],
		'fields'		=> $eg_attach_options['shortcode_auto_fields'],
		'icon'			=> $eg_attach_options['shortcode_auto_icon'],
		'logged_users'  => $eg_attach_options['logged_users_only'],
		'nofollow'  	=> $eg_attach_options['nofollow'],
		'display_label'	=> $eg_attach_options['display_label'],
		'limit' 	 	=> $eg_attach_options['shortcode_auto_limit']
	);
}

$order_by_parameters = array();
foreach ($EG_ATTACH_FIELDS_ORDER_KEY as $key => $value) {
	$order_by_parameters[$key] = __($EG_ATTACH_FIELDS_TITLE[$key], EG_ATTACH_TEXTDOMAIN);
}

$select_fields = array(
	'orderby'      => $order_by_parameters,
	'sortorder'    => array(
		'ASC'      => __('Ascending',  EG_ATTACH_TEXTDOMAIN),
		'DESC'     => __('Descending', EG_ATTACH_TEXTDOMAIN),
	),
	'size'         => array(
		'0'        => __('Large',  EG_ATTACH_TEXTDOMAIN),
		'medium'   => __('Medium', EG_ATTACH_TEXTDOMAIN),
		'small'    => __('Small',  EG_ATTACH_TEXTDOMAIN),
		'custom'   => __('Custom', EG_ATTACH_TEXTDOMAIN)
	),
	'label'        => array(
		'0'        => __('File name',      EG_ATTACH_TEXTDOMAIN),
		'doctitle' => __('Document title', EG_ATTACH_TEXTDOMAIN),
	),
	'doctype'	   => array(
		'all'	   => __('All',      EG_ATTACH_TEXTDOMAIN),
		'0'	   	   => __('Documents', EG_ATTACH_TEXTDOMAIN),
		'image'    => __('Images',    EG_ATTACH_TEXTDOMAIN)
	),
	'force_saveas' => array(
		'-1'       => __('Use default parameter', EG_ATTACH_TEXTDOMAIN),
		'0'        => __('No', EG_ATTACH_TEXTDOMAIN),
		'1'        => __('Yes', EG_ATTACH_TEXTDOMAIN)
	),
	'logged_users' => array(
		'-1'       => __('Use default parameter', EG_ATTACH_TEXTDOMAIN),
		'0'        => __('All users', EG_ATTACH_TEXTDOMAIN),
		'1'        => __('Only logged users', EG_ATTACH_TEXTDOMAIN)
	)
);

function get_select($key, $default_values) {
	global $select_fields;

	$string = '<select id="'.$key.'" name="'.$key.'">';
	foreach ($select_fields[$key] as $id => $value) {
		if ($default_values[$key] == $id) $selected = 'selected'; else $selected = '';
		$string .= '<option value="'.$id.'" '.$selected.'>'.$value.'</option>';
	}
	$string .= '</select>';
	return $string;
}

@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>EG-Attachments</title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript">
		function init() {
			tinyMCEPopup.resizeToInnerSize();
		}

		function getCheckedValue(radioObj) {
			string = "";
			if(!radioObj)
				return "";
			var radioLength = radioObj.length;
			if(radioLength == undefined)
				if(radioObj.checked)
					return radioObj.value;
				else
					return "";
			for(var i = 0; i < radioLength; i++) {
				if(radioObj[i].checked) {
					if (string=="") string = "\"" + radioObj[i].value;
					else string = string + ',' + radioObj[i].value;
				}
			}
			if (string!="") string = string + "\"";
			return string;
		}

		function insertEGAttachmentsShortCode() {

			var orderby   		= document.getElementById('orderby').value;
			var sortorder 		= document.getElementById('sortorder').value;
			var listsize 		= document.getElementById('size').value;
			var doclabel 		= document.getElementById('label').value;
			var doctype 		= document.getElementById('doctype').value;
			var default_fields	= document.getElementById('default_fields');
			var fields_list		= getCheckedValue(document.getElementsByName('fields'));
			var title 			= document.getElementById('title').value;
			var titletag 		= document.getElementById('titletag').value;
			var force_saveas	= parseInt(document.getElementById('force_saveas').value);
			var logged_users	= parseInt(document.getElementById('logged_users').value);
			var limit			= parseInt(document.getElementById('limit').value);
			var nofollow		= document.getElementById('nofollow');
			var display_label	= document.getElementById('display_label');
			var default_doclist	= document.getElementById('default_doclist');
			var doclist 		= getCheckedValue(document.getElementsByName('doclist'));
			
			var tagtext = "[attachments";
			if (sortorder != 'ASC' || orderby != 'title' )
				tagtext = tagtext + " orderby=\"" + orderby + " " + sortorder + "\"";

			if (listsize != 0 )
				tagtext = tagtext + " size=" + listsize;

			if (doclabel != 0 )
				tagtext = tagtext + " label=" + doclabel;

			if (doctype != 0 )
				tagtext = tagtext + " doctype=" + doctype;

			if (! default_fields.checked) {
				if (fields_list!="")
					tagtext = tagtext + " fields=" + fields_list;
			}

			if (title != 0 )
				tagtext = tagtext + " title=\"" + title + "\"";

			if (titletag != "h2" )
				tagtext = tagtext + " titletag=\"" + titletag + "\"";

			if (force_saveas > 0 )
				tagtext = tagtext + " force_saveas=1";

			if (logged_users > 0 )
				tagtext = tagtext + " logged_users=1";

			if (limit > 0 )
				tagtext = tagtext + " limit=" + limit;

			if (nofollow.checked)
				tagtext = tagtext + " nofollow=1";

			if (display_label.checked)
				tagtext = tagtext + " display_label=1";

			if ( default_doclist && !default_doclist.checked) {
				if (doclist!="")
					tagtext = tagtext + " docid=" + doclist;
			}

			var tagtext = tagtext + "]";

			if(window.tinyMCE) {
				window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagtext);
				/* tinyMCEPopup.editor.execCommand('mceRepaint'); */
			}
			tinyMCEPopup.close();
			return;
		}
	</script>
</head>
<body onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';" style="display: none">
	<div class="mceActionPanel">
		<form action="#" method="get" accept-charset="utf-8">

			<div style="float: left; margin:0; width:49%; border-right:1px solid black;">
				<p>
					<label for="orderby"><strong><?php _e('Order by: ',EG_ATTACH_TEXTDOMAIN); ?></strong></label><br />
					<?php echo get_select('orderby', $default_values); ?> <?php echo get_select('sortorder', $default_values); ?>
				</p>
				<p>
					<label for="listsize"><strong><?php _e('List size: ',EG_ATTACH_TEXTDOMAIN); ?></strong></label><br />
					<?php echo get_select('size', $default_values); ?>
				</p>
				<p>
					<label for="doclabel"><strong><?php _e('Document label: ',EG_ATTACH_TEXTDOMAIN); ?></strong></label><br />
					<?php echo get_select('label', $default_values); ?>
				</p>
				<p>
					<label for="doctype"><strong><?php _e('Document type: ',EG_ATTACH_TEXTDOMAIN); ?></strong></label><br />
					<?php echo get_select('doctype', $default_values); ?>
				</p>
				<p>
					<label for="fields"><strong><?php _e('Fields: ',EG_ATTACH_TEXTDOMAIN); ?></strong></label><br />
					<input type="checkbox" id="default_fields" value="default" checked /><?php _e('Display default fields?', EG_ATTACH_TEXTDOMAIN); ?><br />
					<?php
						$i = 0;
						foreach ($EG_ATTACH_FIELDS_TITLE as $id => $value) {
							echo '<input type="checkbox" name="fields" '.(isset($default_values[$id])?'checked':'').' value="'.$id.'" />'.__($value,EG_ATTACH_TEXTDOMAIN).'<br />';
						}
					?>
				</p>
				<p>
					<label for="title"><strong><?php _e('Title: ',EG_ATTACH_TEXTDOMAIN); ?></strong></label><br />
					<input id="title" name="title" type="text" value="" />
				</p>
				<p>
					<label for="titletag"><strong><?php _e('HTML Tag for title: ',EG_ATTACH_TEXTDOMAIN); ?></strong></label><br />
					<input id="titletag" name="titletag" type="text" value="<?php echo $default_values['titletag']; ?>" />
				</p>
			</div>
			<div style="float: left; margin:0 0 0 1%; width:49%">
				<p>
					<label for="force_saveas"><strong><?php _e('Force "saveas": ',EG_ATTACH_TEXTDOMAIN); ?></strong></label><br />
					<?php echo get_select('force_saveas', $default_values); ?>
				</p>
				<p>
					<label for="logged_users"><strong><?php _e('Attachments access: ',EG_ATTACH_TEXTDOMAIN); ?></strong></label><br />
					<?php echo get_select('logged_users', $default_values); ?>
				</p>
				<p>
					<label for="limit"><strong><?php _e('Number of documents to display: ',EG_ATTACH_TEXTDOMAIN); ?></strong></label><br />
					<input type="text" id="limit" value="<?php echo $default_values['limit']; ?>" />
				</p>
				<p>
					<label for="nofollow"><strong><?php _e('Nofollow: ',EG_ATTACH_TEXTDOMAIN); ?></strong></label> 
					<input type="checkbox" id="nofollow" <?php echo ($default_values['nofollow']?'checked':''); ?> />
				</p>
				<p>
					<label for="display_label"><strong><?php _e('Display label: ',EG_ATTACH_TEXTDOMAIN); ?></strong></label>
					<input type="checkbox" id="display_label" <?php echo ($default_values['display_label']>0?'checked':''); ?> /> <br />
					<?php _e('(for size=small only)', EG_ATTACH_TEXTDOMAIN); ?>
				</p>
				<p>
					<label for="doclist"><strong><?php _e('Document list: ',EG_ATTACH_TEXTDOMAIN); ?></strong></label><br />
					<?php
					$attachment_string = __('No attachment available for this post', EG_ATTACH_TEXTDOMAIN);
					if (isset($_GET['post_id'])) {
						$attachment_list = get_children('post_type=attachment&post_parent='.$_GET['post_id']);
						if ($attachment_list) {
							$attachment_string = '<input type="checkbox" id="default_doclist" value="1" checked /> '.__('All',EG_ATTACH_TEXTDOMAIN).'<br />';
							$attachment_string .= '<input type="checkbox" name="doclist" value="first" /> '.__('First',EG_ATTACH_TEXTDOMAIN).'<br />';
							foreach ($attachment_list as $key => $attachment) {
								$attachment_string .= '<input type="checkbox" name="doclist" value="'.$attachment->ID.'" /> '.$attachment->post_title.'<br />';
							}
							$attachment_string .= '<input type="checkbox" name="doclist" value="last" /> '.__('Last',EG_ATTACH_TEXTDOMAIN).'<br />';
						}
					}
					echo $attachment_string;
					?>
				</p>
			</div>
		</form>
	</div>
	<br style="clear: both;" />
	<div class="mceActionPanel">
		<div style="float: left">
			<input type="submit" id="insert" name="insert" value="<?php _e("Insert", EG_ATTACH_TEXTDOMAIN); ?>" onclick="insertEGAttachmentsShortCode();" />
		</div>
		<div style="float: right">
			<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", EG_ATTACH_TEXTDOMAIN); ?>" onclick="tinyMCEPopup.close();" />
		</div>
	</div>
</body>
</html>