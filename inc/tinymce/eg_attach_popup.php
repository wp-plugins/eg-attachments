<?php

require_once( dirname( dirname( dirname(__FILE__) ) ) .'/eg-attachments-bootstrap.php');
//Check for rights
if ( !is_user_logged_in() || !current_user_can('edit_posts') )
	wp_die(__("You are not allowed to access this file."));

if ( ! defined('EGA_OPTIONS_ENTRY') ) {
	require_once( dirname( __FILE__ ) . '/eg-attachments-config.inc.php');
}

$eg_attach_options = get_option(EGA_OPTIONS_ENTRY);

$default_values = $EG_ATTACHMENT_SHORTCODE_DEFAULTS;
list($default_values['orderby'], $default_values['sortorder']) = explode(' ', $EG_ATTACHMENT_SHORTCODE_DEFAULTS['orderby'] );
$default_values['force_saveas'] = $eg_attach_options['force_saveas'];
$default_values['logged_users'] = $eg_attach_options['logged_users_only'];
$default_values['nofollow']  	= $eg_attach_options['nofollow'];

$current_values = $default_values;
if ($eg_attach_options['shortcode_auto_default_opts']) {
	$current_values = array_merge($current_values, array(
		'orderby'  		=> $eg_attach_options['shortcode_auto_orderby'],
		'order'			=> $eg_attach_options['shortcode_auto_order'],
		'size'     		=> $eg_attach_options['shortcode_auto_size'],
		'doctype'  		=> $eg_attach_options['shortcode_auto_doc_type'],
		'docid'    		=> 0,
		'title'    		=> $eg_attach_options['shortcode_auto_title'],
		'titletag' 		=> $eg_attach_options['shortcode_auto_title_tag'],
		'label'    		=> $eg_attach_options['shortcode_auto_label'],
		'fields'		=> $eg_attach_options['shortcode_auto_fields'],
		'icon'			=> $eg_attach_options['shortcode_auto_icon'],
		'limit' 	 	=> $eg_attach_options['shortcode_auto_limit'])
	);
}

$order_by_parameters = array();
foreach ($EG_ATTACH_FIELDS_ORDER_KEY as $key => $value) {
	$order_by_parameters[$key] = __($EG_ATTACH_FIELDS_TITLE[$key], EGA_TEXTDOMAIN);
}

$select_fields = array(
	'orderby'      => $order_by_parameters,
	'sortorder'    => array(
		'ASC'      => __('Ascending',  EGA_TEXTDOMAIN),
		'DESC'     => __('Descending', EGA_TEXTDOMAIN),
	),
	'size'         => array(
		'large'    => __('Large',  EGA_TEXTDOMAIN),
		'medium'   => __('Medium', EGA_TEXTDOMAIN),
		'small'    => __('Small',  EGA_TEXTDOMAIN),
		'custom'   => __('Custom', EGA_TEXTDOMAIN)
	),
	'label'        => array(
		'filename' => __('File name',      EGA_TEXTDOMAIN),
		'doctitle' => __('Document title', EGA_TEXTDOMAIN),
	),
	'doctype'	   => array(
		'all'	   => __('All',       EGA_TEXTDOMAIN),
		'document' => __('Documents', EGA_TEXTDOMAIN),
		'image'    => __('Images',    EGA_TEXTDOMAIN)
	),
	'force_saveas' => array(
		'-1'       => __('Use default parameter', EGA_TEXTDOMAIN),
		'0'        => __('No', EGA_TEXTDOMAIN),
		'1'        => __('Yes', EGA_TEXTDOMAIN)
	),
	'logged_users' => array(
		'-1'       => __('Use default parameter', EGA_TEXTDOMAIN),
		'0'        => __('All users', EGA_TEXTDOMAIN),
		'1'        => __('Only logged users', EGA_TEXTDOMAIN)
	)
);

function get_select($html_id, $key, $current_values, $default_values) {
	global $select_fields;

	$string = '<select id="'.$html_id.'" name="'.$html_id.'">';
	foreach ($select_fields[$key] as $id => $value) {
		if ($current_values[$key] == $id) $selected = 'selected'; else $selected = '';
		$string .= '<option value="'.$id.'" '.$selected.'>'.$value.'</option>';
	}
	$string .= '</select><input type="hidden" name="'.$html_id.'_def" id="'.$html_id.'_def" value="'.$default_values[$key].'" />';
	return $string;
}

// Preparing list of tags, if required
$tags_select_string = '';
if ($eg_attach_options['tags_assignment']) {

	// Get all terms (tags)
	$tags_list = get_terms('post_tag');

	foreach ($tags_list as $tag) {
		$tags_select_string .= '<option value="'.$tag->slug.'" /> '.
					htmlspecialchars($tag->name).
					'</option>';
	} // End of foreach
	if ($tags_select_string != '')
		$tags_select_string = '<select multiple size="10" name="tags" id="tags">'.
								'<option value="none"> </option>'.
								$tags_select_string.
								'</select>';
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

			var orderby   		 = document.getElementById('orderby').value;
			var orderby_def		 = document.getElementById('orderby_def').value;
			var sortorder 		 = document.getElementById('sortorder').value;
			var sortorder_def	 = document.getElementById('sortorder_def').value;
			var listsize 		 = document.getElementById('size').value;
			var listsize_def	 = document.getElementById('size_def').value;
			var doclabel 		 = document.getElementById('label').value;
			var doclabel_def	 = document.getElementById('label_def').value;
			var doctype 		 = document.getElementById('doctype').value;
			var doctype_def		 = document.getElementById('doctype_def').value;
			var default_fields	 = document.getElementById('default_fields');
			var fields_list		 = getCheckedValue(document.getElementsByName('fields'));
			var title 			 = document.getElementById('title').value;
			var title_def 		 = document.getElementById('title_def').value;
			var titletag 		 = document.getElementById('titletag').value;
			var titletag_def 	 = document.getElementById('titletag_def').value;
			var force_saveas	 = parseInt(document.getElementById('force_saveas').value);
			var force_saveas_def = parseInt(document.getElementById('force_saveas_def').value);
			var logged_users	 = parseInt(document.getElementById('logged_users').value);
			var logged_users_def = parseInt(document.getElementById('logged_users_def').value);
			var limit			 = parseInt(document.getElementById('limit').value);
			var limit_def		 = parseInt(document.getElementById('limit_def').value);
			var nofollow		 = document.getElementById('nofollow');
			var nofollow_def	 = parseInt(document.getElementById('nofollow_def').value)
			var default_doclist	 = document.getElementById('default_doclist');
			var doclist 		 = getCheckedValue(document.getElementsByName('doclist'));
			var taglist			 = document.getElementById('tags');

			var tagtext = "[attachments";
			if (sortorder != sortorder_def || orderby != orderby_def )
				tagtext = tagtext + " orderby=\"" + orderby + " " + sortorder + "\"";

			if (listsize != listsize_def )
				tagtext = tagtext + " size=" + listsize;

			if (doclabel != doclabel_def )
				tagtext = tagtext + " label=" + doclabel;

			if (doctype != doctype_def )
				tagtext = tagtext + " doctype=" + doctype;

			if (! default_fields.checked) {
				if (fields_list!="")
					tagtext = tagtext + " fields=" + fields_list;
			}

			if (title != title_def )
				tagtext = tagtext + " title=\"" + title + "\"";

			if (titletag != titletag_def )
				tagtext = tagtext + " titletag=\"" + titletag + "\"";

			if (force_saveas > force_saveas_def )
				tagtext = tagtext + " force_saveas=1";

			if (logged_users > logged_users_def )
				tagtext = tagtext + " logged_users=1";

			if (limit != limit_def )
				tagtext = tagtext + " limit=" + limit;

			if (nofollow.checked)
				nofollow_val=1
			else
				nofollow_val=0

			if (nofollow_val != nofollow_def)
				tagtext = tagtext + " nofollow=" + nofollow_val;

			if ( default_doclist && !default_doclist.checked) {
				if (doclist!="")
					tagtext = tagtext + " docid=" + doclist;
			}

			var values = '';
			for(var i=0; i< taglist.options.length; i++)	{
				if (taglist.options[i].selected == true && taglist.options[i].value != "none") {
					if (values=='') values = taglist.options[i].value;
					else values = values + ',' + taglist.options[i].value;
				}
			}
			if (values != '') {
				tagtext = tagtext + " tags=\"" + values + "\"";
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
					<label for="orderby"><strong><?php _e('Order by: ',EGA_TEXTDOMAIN); ?></strong></label><br />
					<?php echo get_select('orderby', 'orderby', $current_values, $default_values); ?> <?php echo get_select('sortorder', 'sortorder', $current_values, $default_values); ?>
				</p>
				<p>
					<label for="listsize"><strong><?php _e('List size: ',EGA_TEXTDOMAIN); ?></strong></label><br />
					<?php echo get_select('size', 'size', $current_values, $default_values); ?>
				</p>
				<p>
					<label for="doclabel"><strong><?php _e('Document label: ',EGA_TEXTDOMAIN); ?></strong></label><br />
					<?php echo get_select('label', 'label', $current_values, $default_values); ?>
				</p>
				<p>
					<label for="doctype"><strong><?php _e('Document type: ',EGA_TEXTDOMAIN); ?></strong></label><br />
					<?php echo get_select('doctype', 'doctype', $current_values, $default_values); ?>
				</p>
				<p>
					<label for="fields"><strong><?php _e('Fields: ',EGA_TEXTDOMAIN); ?></strong></label><br />
					<input type="checkbox" id="default_fields" value="default" checked /><?php _e('Display default fields?', EGA_TEXTDOMAIN); ?><br />
					<?php
						$i = 0;
						foreach ($EG_ATTACH_FIELDS_TITLE as $id => $value) {
							echo '<input type="checkbox" name="fields" '.(isset($default_values[$id])?'checked':'').' value="'.$id.'" />'.__($value,EGA_TEXTDOMAIN).'<br />';
						}
					?>
				</p>
				<p>
					<label for="title"><strong><?php _e('Title: ',EGA_TEXTDOMAIN); ?></strong></label><br />
					<input id="title" name="title" type="text" value="<?php echo $default_values['title']; ?>" />
					<input type="hidden" name="title_def" id="title_def" value="<?php echo $default_values['title']; ?>" />
				</p>
				<p>
					<label for="titletag"><strong><?php _e('HTML Tag for title: ',EGA_TEXTDOMAIN); ?></strong></label><br />
					<input id="titletag" name="titletag" type="text" value="<?php echo $default_values['titletag']; ?>" />
					<input type="hidden" name="titletag_def" id="titletag_def" value="<?php echo $default_values['titletag']; ?>" />
				</p>
			</div>
			<div style="float: left; margin:0 0 0 1%; width:49%">
				<p>
					<label for="force_saveas"><strong><?php _e('Force "saveas": ',EGA_TEXTDOMAIN); ?></strong></label><br />
					<?php echo get_select('force_saveas', 'force_saveas', $current_values, $default_values); ?>
				</p>
				<p>
					<label for="logged_users"><strong><?php _e('Attachments access: ',EGA_TEXTDOMAIN); ?></strong></label><br />
					<?php echo get_select('logged_users', 'logged_users', $current_values, $default_values); ?>
				</p>
				<p>
					<label for="limit"><strong><?php _e('Number of documents to display: ',EGA_TEXTDOMAIN); ?></strong></label><br />
					<input type="text" id="limit" value="<?php echo $default_values['limit']; ?>" />
					<input type="hidden" name="limit_def" id="limit_def" value="<?php echo $default_values['limit']; ?>" />
				</p>
				<p>
					<label for="nofollow"><strong><?php _e('Nofollow: ',EGA_TEXTDOMAIN); ?></strong></label>
					<input type="checkbox" id="nofollow" <?php echo ($default_values['nofollow']>0?'checked':''); ?> />
					<input type="hidden" name="nofollow_def" id="nofollow_def" value="<?php echo $default_values['nofollow']; ?>" />
				</p>
<?php /*
				<p>
					<label for="display_label"><strong><?php _e('Display label of fields: ',EGA_TEXTDOMAIN); ?></strong></label>
					<input type="checkbox" id="display_label" <?php echo ($default_values['display_label']>0?'checked':''); ?> /> <br />
					<?php _e('(for size=small only)', EGA_TEXTDOMAIN); ?>
				</p>
*/ ?>
				<p>
					<label for="doclist"><strong><?php _e('Document list: ',EGA_TEXTDOMAIN); ?></strong></label><br />
					<?php
					$attachment_string = __('No attachment available for this post', EGA_TEXTDOMAIN);
					if (isset($_GET['post_id'])) {
						$attachment_list = get_children('post_type=attachment&post_parent='.$_GET['post_id']);
						if ($attachment_list) {
							$attachment_string = '<input type="checkbox" id="default_doclist" value="1" checked /> '.__('All',EGA_TEXTDOMAIN).'<br />';
							$attachment_string .= '<input type="checkbox" name="doclist" value="first" /> '.__('First',EGA_TEXTDOMAIN).'<br />';
							foreach ($attachment_list as $key => $attachment) {
								$attachment_string .= '<input type="checkbox" name="doclist" value="'.$attachment->ID.'" /> '.$attachment->post_title.'<br />';
							}
							$attachment_string .= '<input type="checkbox" name="doclist" value="last" /> '.__('Last',EGA_TEXTDOMAIN).'<br />';
						}
					}
					echo $attachment_string;
					?>
				</p>
				<?php if ($tags_select_string != '') { ?>
				<p>
					<label for="tags"><strong><?php _e('Filter attachments using tags', EGA_TEXTDOMAIN); ?></strong></label><br />
					<?php echo $tags_select_string; ?>
				</p>
				<?php } ?>
			</div>
		</form>
	</div>
	<br style="clear: both;" />
	<div class="mceActionPanel">
		<div style="float: left">
			<input type="submit" id="insert" name="insert" value="<?php _e("Insert", EGA_TEXTDOMAIN); ?>" onclick="insertEGAttachmentsShortCode();" />
		</div>
		<div style="float: right">
			<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", EGA_TEXTDOMAIN); ?>" onclick="tinyMCEPopup.close();" />
		</div>
	</div>
</body>
</html>