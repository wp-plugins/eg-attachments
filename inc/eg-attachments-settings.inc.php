<?php

global $EG_ATTACH_FIELDS_TITLE, $EG_ATTACH_FIELDS_ORDER_KEY, $EG_ATTACH_DEFAULT_FIELDS;

$this->options_form = new EG_Form_211('ega_options', 'EG-Attachements Settings', $this->options_entry, $this->textdomain, '', '',
										array(&$this, 'display_sidebar'));

//$this->options_form->set_debug_mode(TRUE, dirname(EGA_COREFILE).'/debug.log');

/*
$this->options_form->add_tab('behavior', 'General shortcode behavior', 'All of the following parameters are applied on manual AND automatic shortcode.');
$this->options_form->add_tab('auto_shortcode',  'Automatic Shortcode');
$this->options_form->add_tab('admin', 'Administration');
*/
$shortcode_behavior_section_id = $this->options_form->add_section( array( 'tab' => 'behavior', 'title' => 'General behavior of shortcodes'));


	
	$this->options_form->add_field( array(
			'section'	=> $shortcode_behavior_section_id,
			'name'		=> 'link',
			'label'		=> 'Links to attachments',
			'type'		=> 'radio',
			'options'	=> array(
				'link' => '<strong>Permalink</strong>. <br /><em>The URL of the attachment is using defined permalink structure. For example: <code>http://blog url/post url/attachment slug/</code>, or <code>http://blog url/?p=xx</code>.<br/>The attachment will be displayed inside your blog, as a post.</em>',
				'file' => '<strong>File</strong>. <br /><em>The URL is the document\'s address. For example: <code>http://blog url/wp-content/upload/2011/09/file name.file extension/</code>.<br />When you click on the attachment, it is displayed directly, as a document, and not as a page of blog. WordPress mechanisms are not used</em>',
				'direct'    => '<strong>Direct</strong>. <br /><em>It\'s the default behavior of the plugin. The URL is pointing to the file, but in this case, the link is encoded. When you click on the attachment, it is displayed as a document, and not as a page of blog. WordPress mechanisms are used</em>'),
			'after' => '<br />All features cannot be implemented with all of these modes.<br /><table class="eg-attach-help"><tr><th>Link type</th><th>Statistics</th><th>Force "Save as"</th><th>Security</th></tr><tr><th class="row">Permalink</th><td>x</td><td>x</td><td>x</td></tr><tr><th class="row">File</th><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><th class="row">Direct</th><td>x</td><td>x</td><td>x</td></tr></table>'
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $shortcode_behavior_section_id,
			'name'		=> 'display_label',
			'label'		=> 'Display label of fields',
			'type'		=> 'checkbox',
			'after'		=> 'For Small size only, check the box if you want display labels',
			'desc'		=> '(For the other sizes, labels are always displayed)'
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $shortcode_behavior_section_id,
			'name'		=> 'date_format',
			'label'		=> 'Date format',
			'type'		=> 'text',
			'size'		=> 'small',
			'after'		=> __('Default value: ', $this->textdomain).get_option('date_format').' ('.date_i18n(get_option('date_format')).')',
			'desc'		=> 'Use PHP <strong>date()</strong> parameters to configure the date:<br />d&nbsp;&nbsp;&nbsp;&nbsp;Day of the month, 2 digits with leading zeros<br />D&nbsp;&nbsp;&nbsp;&nbsp;A textual representation of a day, three letters<br />j&nbsp;&nbsp;&nbsp;&nbsp;Day of the month without leading zeros<br />S&nbsp;&nbsp;&nbsp;&nbsp;English ordinal suffix for the day of the month, 2 characters<br />F&nbsp;&nbsp;&nbsp;&nbsp;A full textual representation of a month, such as January or March<br />m&nbsp;&nbsp;&nbsp;&nbsp;Numeric representation of a month, with leading zeros<br />M&nbsp;&nbsp;&nbsp;&nbsp;A short textual representation of a month, three letters<br />n&nbsp;&nbsp;&nbsp;&nbsp;Numeric representation of a month, without leading zeros<br />Y&nbsp;&nbsp;&nbsp;&nbsp;A full numeric representation of a year, 4 digits<br />y&nbsp;&nbsp;&nbsp;&nbsp;A two digit representation of a year'
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $shortcode_behavior_section_id,
			'name'		=> 'nofollow',
			'label'		=> '&laquo;Nofollow&raquo; attribute',
			'type'		=> 'checkbox',
			'after'		=> 'Check if you want to automatically add rel="nofollow" to attachment links'
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $shortcode_behavior_section_id,
			'name'		=> 'force_saveas',
			'label'		=> '"Save As" activation',
			'type'		=> 'checkbox',
			'after'		=> 'Force "Save As" when users click on the attachments',
			'desc'		=> 'In normal mode, when you click on the attachments\' links, according their mime type, documents are displayed, or a dialog box appears to choose \'run with\' or \'Save As\'. By activating the following option, the dialog box will appear for all cases.'
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $shortcode_behavior_section_id,
			'name'		=> 'logged_users_only',
			'label'		=> 'Attachments access',
			'type'		=> 'checkbox',
			'after'		=> 'Restrict access to the attachments to logged users only!',
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $shortcode_behavior_section_id,
			'name'		=> 'login_url',
			'label'		=> 'Url to login or register page:',
			'type'		=> 'text'
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $shortcode_behavior_section_id,
			'name'		=> 'stats_enable',
			'label'		=> 'Click counter',
			'type'		=> 'checkbox',
			'after'		=> 'Activate statistics',
			'desc'		=> 'Record all clicks occuring in the listed attachements.',
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $shortcode_behavior_section_id,
			'name'		=> 'stats_ip_exclude',
			'label'		=> 'Statistics, IP to exclude',
			'type'		=> 'text',
			'desc'		=> 'List of IP address you want to exclude',
		)
	);

$custom_format_section_id = $this->options_form->add_section( array(
	'tab' 		=> 'behavior',
	'title' 	=> 'Custom format',
	'header'	=> 'You can define your own display format, using <strong>custom</strong>. Parameters of this section will be applied only if you use <em>Custom</em> as display format for auto-shortcode (fifth option of this page), or if you specify <em>size=custom</em> in the shortcodes in your post.<br />Keywords you can use are listed below.',
	'footer'	=> 'Available keywords:<br /><table class="eg-attach-custom-format"><tr><td><strong>%LINK%</strong></td><td>Full link of document (such as &lt;a href="..."&gt;...), </td></tr><tr><td><strong>%URL%</strong></td><td>url of document (permalink),</td></tr><tr><td><strong>%GUID%</strong></td><td>direct link to document as stored in the WP database,</td></tr><tr><td><strong>%ICONURL%</strong></td><td>URL of icon,</td></tr><tr><td><strong>%TITLE%</strong></td><td>Title of the document,</td></tr><tr><td><strong>%CAPTION%</strong></td><td>caption of the document,</td></tr><tr><td><strong>%DESCRIPTION%</strong></td><td>description of the document,</td></tr><tr><td><strong>%FILENAME%</strong></td><td>Name of the attached file,</td></tr><tr><td><strong>%FILESIZE%</strong></td><td>Size of the attached document,</td></tr><tr><td><strong>%ATTID%</strong></td><td>ID of the attachment.</td></tr><tr><td><strong>%TYPE%</strong></td><td>Type of the documents</td></tr><tr><td><strong>%DATE%</strong></td><td>Date of last modification</td></tr></table>'
	)
);

	$this->options_form->add_field( array(
			'section'	=> $custom_format_section_id,
			'name'		=> 'custom_format_pre',
			'label'		=> 'Custom format, before list: ',
			'type'		=> 'textarea',
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $custom_format_section_id,
			'name'		=> 'custom_format',
			'label'		=> 'Custom list format: ',
			'type'		=> 'textarea'
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $custom_format_section_id,
			'name'		=> 'custom_format_post',
			'label'		=> 'Custom format, after list: ',
			'type'		=> 'textarea'
		)
	);

$auto_shortcode_section_id = $this->options_form->add_section( array( 'tab' => 'auto_shortcode', 'title' => 'Auto shortcode'));

	$this->options_form->add_field( array(
			'section'	=> $auto_shortcode_section_id,
			'name'		=> 'shortcode_auto',
			'label'		=> 'Activation',
			'desc'		=> 'With this option, you can automaticaly add the list of attachments in your blog, without using shortcode',
			'type'		=> 'select',
			'options'	=> array( 0 => 'Not activated', 2 => 'At the end', 3 => 'Before the excerpt', 4 => 'Between excerpt and content')
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $auto_shortcode_section_id,
			'name'		=> 'shortcode_auto_exclusive',
			'label'		=> 'Auto / Manual',
			'after'		=>  'Disable auto-shortcode when a manual shortcode is detected',
			'desc'		=> 'If this option is activated, auto shortcode won\'t be generated if a manual shortcode is detected in the post being displayed',
			'type'		=> 'checkbox',
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $auto_shortcode_section_id,
			'name'		=> 'shortcode_auto_where',
			'label'		=> 'Where',
			'before'	=> 'Activate automatic shortcode only on the following cases',
			'type'		=> 'checkbox',
			'options'	=> array( 'home' => 'Homepage,', 'post' => 'Posts,', 'page' => 'Pages,', 'index' => 'Lists of posts (archives, categories, ...).')
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $auto_shortcode_section_id,
			'name'		=> 'shortcode_auto_title',
			'label'		=> 'Title of the list: ',
			'type'		=> 'text',
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $auto_shortcode_section_id,
			'name'		=> 'shortcode_auto_title_tag',
			'label'		=> 'HTML Tag for title: ',
			'type'		=> 'text',
			'size'		=> 'small',
			'after'		=> '(tag like h2, or h3, ...)'
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $auto_shortcode_section_id,
			'name'		=> 'shortcode_auto_size',
			'label'		=> 'List format: ',
			'type'		=> 'radio',
			'options'	=> array( 'small' => 'Small', 'medium' => 'Medium', 'large' => 'Large', 'custom' => 'Custom')
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $auto_shortcode_section_id,
			'name'		=> 'shortcode_auto_orderby',
			'label'		=> 'Order by: ',
			'type'		=> 'select',
			'options'	=> array_intersect_key($EG_ATTACH_FIELDS_TITLE, $EG_ATTACH_FIELDS_ORDER_KEY)
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $auto_shortcode_section_id,
			'name'		=> 'shortcode_auto_order',
			'label'		=> 'Sort Order: ',
			'type'		=> 'select',
			'options'	=> array( 'ASC' => 'Ascending', 'DESC' => 'Descending')
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $auto_shortcode_section_id,
			'name'		=> 'shortcode_auto_icon',
			'label'		=> 'Display icons: ',
			'after'		=> 'Check the box to display icons',
			'type'		=> 'checkbox'
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $auto_shortcode_section_id,
			'name'		=> 'shortcode_auto_limit',
			'label'		=> 'Number of documents to display: ',
			'after'		=> '(Default: -1, for all document are listed)',
			'type'		=> 'text',
			'size'		=> 'small'
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $auto_shortcode_section_id,
			'name'		=> 'shortcode_auto_doc_type',
			'label'		=> 'Document type: ',
			'type'		=> 'radio',
			'options'	=> array( 'all' => 'All', 'document' => 'Documents', 'image' => 'Images')
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $auto_shortcode_section_id,
			'name'		=> 'shortcode_auto_label',
			'label'		=> 'Documents label: ',
			'before'	=> 'Choose the field that will be displayed as title of documents',
			'type'		=> 'radio',
			'options'	=> array( 'filename' => 'File name', 'doctitle' => 'Document title')
		)
	);

	$default_fields = __('The default list is: ', $this->textdomain).'<br />';
	foreach ($EG_ATTACH_DEFAULT_FIELDS as $list_size => $fields_list) {
		$default_fields .= '<strong>'.__(ucfirst(strtolower($list_size)).' list:',$this->textdomain).'</strong> ';
		foreach ($fields_list as $value) {
			$default_fields .= __($EG_ATTACH_FIELDS_TITLE[$value], $this->textdomain).', ';
		}
		$default_fields .= '<br />';
	} // End of foreach

	$this->options_form->add_field( array(
			'section'	=> $auto_shortcode_section_id,
			'name'		=> 'shortcode_auto_fields_def',
			'label'		=> 'Fields: ',
			'type'		=> 'checkbox',
			'after'		=> 'Do you want to display default fields?',
			'desc'		=> $default_fields
		)
	);

	$field_values = $this->options['shortcode_auto_fields'];
	if (! is_array($field_values)) $field_values = explode(',', $field_values);
	for ($i=sizeof($field_values)+1; $i<=sizeof($EG_ATTACH_FIELDS_TITLE); $i++) {
		$field_values[$i]= '0';
	}
	// Cannot use array_pad, wants to start key at 1

	$fields_grid = array( 'header' => array('Position', 'Field') );
	for ($i=1; $i<=sizeof($field_values); $i++) {
		$fields_grid['list'][] = array( 'value' => $i, 'select' => array_merge(array( ' '),$EG_ATTACH_FIELDS_TITLE));
	}
	$this->options_form->add_field( array(
			'section'	=> $auto_shortcode_section_id,
			'name'		=> 'shortcode_auto_fields',
			'label'		=> 'Custom fields:',
			'before'	=> 'or display the following fields',
			'type'		=> 'grid_select',
			'options'	=> $fields_grid
		)
	);

	$this->options_form->add_field( array(
			'section'	=> $auto_shortcode_section_id,
			'name'		=> 'shortcode_auto_default_opts',
			'label'		=> 'Default options? ',
			'after'		=> 'Do you want that auto shortcode options become the default options for the TinyMCE EG-Attachments Editor?',
			'type'		=> 'checkbox'
		)
	);

$styles_section_id = $this->options_form->add_section( array( 'tab' => 'admin', 'title' => 'Styles'));

	$this->options_form->add_field( array(
			'section'	=> $styles_section_id,
			'name'		=> 'load_css',
			'label'		=> 'Styles',
			'type'		=> 'checkbox',
			'after'		=> 'Check if you want to use the plugin stylesheet file, uncheck if you want to use your own styles, or include styles on the theme stylesheet.'
		)
	);

$admin_section_id = $this->options_form->add_section( array( 'tab' => 'admin', 'title' => 'Administration interface'));

	$this->options_form->add_field( array(
			'section'	=> $admin_section_id,
			'name'		=> 'use_metabox',
			'label'		=> 'Post editor page',
			'type'		=> 'checkbox',
			'after'		=> 'Show metabox to display list of attachments of the current post/page'
		)
	);
	$this->options_form->add_field( array(
			'section'	=> $admin_section_id,
			'name'		=> 'comment_status',
			'label'		=> 'Comments',
			'type'		=> 'select',
			'options'	=> array( 'default' => 'Keep default', 'open' => 'Allow the comments', 'closed' => 'Closed the comments'),
			'before'	=> 'When you upload an attachment file, which value to you want for comments'
		)
	);
	$this->options_form->add_field( array(
			'section'	=> $admin_section_id,
			'name'		=> 'ping_status',
			'label'		=> 'Pingbacks/Trackbacks',
			'type'		=> 'select',
			'options'	=> array( 'default' => 'Keep default', 'open' => 'Allow the pings', 'closed' => 'Closed the pings'),
			'before'	=> 'When you upload an attachment file, which value to you want for pingbacks / trackbacks'
		)
	);

$uninstall_section_id = $this->options_form->add_section( array( 'tab' => 'admin', 'title' => 'Uninstall options'));

	$this->options_form->add_field( array(
			'section'	=> $uninstall_section_id,
			'name'		=> 'uninstall_del_options',
			'label'		=> 'Options',
			'type'		=> 'checkbox',
			'after'		=> 'Delete options during uninstallation.',
			'desc'		=> 'Be careful: these actions cannot be cancelled. All plugin\'s options will be deleted while plugin uninstallation.'
		)
	);


?>