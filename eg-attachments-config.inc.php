<?php

define('EG_ATTACH_TEXTDOMAIN',    'eg-attachments');
define('EG_ATTACH_SHORTCODE',     'attachments');
define('EG_ATTACH_REPOSITORY', 'http://wordpress.org/extend/plugins/eg-attachments');

$EG_ATTACH_FIELDS_TITLE = array(
	'id'			=> 'Document id',
	'label'			=> 'Document label',
	'title' 		=> 'Title',
	'caption' 		=> 'Caption',
	'description' 	=> 'Description',
	'filename'		=> 'File name',
	'size'			=> 'Size',
	'small_size'	=> 'Small size',
	'date'			=> 'Date',
	'type'			=> 'Type',
);

$EG_ATTACH_FIELDS_ORDER_KEY = array(
	'id'			=> 'ID',
	'title' 		=> 'post_title',
	'caption' 		=> 'post_excerpt',
	'description' 	=> 'post_content',
	'filename'		=> 'post_name',
	'size'			=> '',
	'date'			=> 'post_date',
	'type'			=> 'post_mime_type',
);

$EG_ATTACH_DEFAULT_OPTIONS = array(
	'shortcode_auto'			  => 0, /* 0='Not activated', 1=no more used, 2=At the end, 3=Before the excerpt, 4=Between excerpt and content */
	'shortcode_auto_exclusive'	  => 0,
	'shortcode_auto_where'		  => 'post',
	'shortcode_auto_title'  	  => '',
	'shortcode_auto_title_tag'	  => 'h2',
	'shortcode_auto_size'		  => 'large',
	'shortcode_auto_doc_type'	  => 'document',
	'shortcode_auto_orderby'	  => 'title',
	'shortcode_auto_order'		  => 'ASC',
	'shortcode_auto_label'		  => 'filename',
	'shortcode_auto_fields_def'	  => 1,
	'shortcode_auto_fields'		  => array_values($EG_ATTACH_FIELDS_TITLE),
	'shortcode_auto_icon'		  => 1,
	'shortcode_auto_default_opts' => 0,
	'shortcode_auto_limit'		  => -1,
	'custom_format_pre'	  	  	  => '<ul>',
	'custom_format'		  		  => '<li><a href="%URL%" title="%TITLE%">%TITLE%</a></li>',
	'custom_format_post'  		  => '</ul>',
	'force_saveas' 				  => 0,
	'logged_users_only'			  => 0,
	'login_url'					  => '',
	'uninstall_del_options'		  => 0,
	'stats_enable'				  => 0,
	'clicks_table'				  => 0,
	'stats_ip_exclude'			  => '',
	'load_css'					  => 1,
	'use_metabox'				  => 0,
	'nofollow'				  	  => 0,
	'display_label'				  => 0,
	'date_format'				  => ''
);

$EG_ATTACHMENT_SHORTCODE_DEFAULTS = array(
	'orderby'  		=> 'title ASC',
	'size'     		=> 'large',
	'doctype'  		=> 'document',
	'docid'    		=> 0,
	'title'    		=> '',
	'titletag' 		=> 'h2',
	'label'    		=> 'filename',
	'force_saveas'	=> -1,
	'fields'		=> '',
	'icon'			=> 1,
	'format_pre'	=> '',
	'format'		=> '',
	'format_post'	=> '',
	'logged_users'  => -1,
	'id'            => 0,
	'limit'			=> -1,
	'nofollow'		=> 0,
	'display_label' => -1
);


$EG_ATTACH_DEFAULT_FIELDS = array(
	'small' 	=> array( 1 => 'label', 2 => 'small_size'),
	'medium'	=> array( 1 => 'label', 2 => 'small_size', 	3 => 'caption'),
	'large'		=> array( 1 => 'title', 2 => 'caption', 	3 => 'filename', 4 => 'size')
);

?>