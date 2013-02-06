<?php

define('EGA_TEXTDOMAIN',  		'eg-attachments' );
define('EGA_OPTIONS_ENTRY',		'EG-Attachments-Options');
define('EGA_OPTIONS_PAGE_ID', 	'ega_options');
define('EGA_SHORTCODE',     	'attachments');
define('EGA_TEMPLATE_POST_TYPE','egatmpl');

define('EGA_READ_TEMPLATES', 	'edit_posts');
define('EGA_EDIT_TEMPLATES', 	'publish_pages');
define('EGA_CREATE_TEMPLATES', 	'publish_pages');
define('EGA_DELETE_TEMPLATES', 	'delete_others_posts');
define('EGA_VIEW_STATS', 		EGA_READ_TEMPLATES);

define('EGA_TEMPLATE_CACHE_EXPIRATION', 604800); /* 1 week = 7 * 24 * 3600 */
define('EGA_SHORTCODE_CACHE_EXPIRATION', 86400); /* 1 day = 24 * 3600 */

$EGA_DEFAULT_OPTIONS = array(
		'load_css'					  => 1, 		/* tested */
		'uninstall_del_options'		  => 0,
		'display_admin_bar'			  => 1, 		/* tested */
		'tinymce_button'			  => 1, 		/* tested */
		'use_metabox'				  => 0,			/* tested */
		'shortcode_auto'			  => 0, 		/* 0='Not activated', 1=no more used, 2=At the end, 3=Before the excerpt, 4=Between excerpt and content */ 													/* tested */
		'shortcode_auto_exclusive'	  => 0, 		/* tested */
		'shortcode_auto_where'		  => array('post', 'page'),
		'shortcode_auto_title'  	  => '', 		/* tested */
		'shortcode_auto_title_tag'	  => 'h2', 		/* tested */
		'shortcode_auto_template'	  => 'large', 	/* tested */
		'shortcode_auto_doc_type'	  => 'document', /* tested */
		'shortcode_auto_orderby'	  => 'title', 	/* tested */
		'shortcode_auto_order'		  => 'ASC', 	/* tested */
		'shortcode_auto_limit'		  => -1, 		/* tested */
		'shortcode_auto_default_opts' => 0, 		/* tested */
		'clicks_table'				  => 0, 		/* tested */
		'standard_templates'		  => '', 		/* array('large', 'large-list', 'medium', 'medium-list', 'small', 'small-list'), */
		'force_saveas' 				  => 0,			/* tested */
		'logged_users_only'			  => 0, 		/* tested */
		'login_url'					  => '', 		/* tested */
		'stats_enable'				  => 0, 		/* tested */
		'stats_ip_exclude'			  => '',
		'purge_stats'				  => 24,		/* tested */
		'date_format'				  => '',		/* tested */
		'tags_assignment'			  => 0,			/* tested */
		'icon_path'					  => '',		/* tested */
		'icon_url'					  => '',		/* tested */
		'link'						  => 'direct',	/* tested */
		'nofollow'				  	  => 0,			/* tested */
		'target_blank'				  => 0,			/* tested */
		'exclude_thumbnail'			  => 0,			/* tested */
		'legacy_custom_format'		  => ''
	);

$EGA_SHORTCODE_DEFAULTS = array(
	'title'    		=> '',			/* Tested */
	'titletag' 		=> 'h2',		/* Tested */
	'orderby'  		=> 'title ASC',	/* Tested */
	'template'		=> '',			/* Tested */
	'size'     		=> 'large',		/* Tested */
	'doctype'  		=> 'document',	/* Tested */
	'limit'			=> -1,			/* Tested */
	'docid'    		=>  0,			/* Tested */
	'id'            =>  0,			/* Tested */
	'force_saveas'	=> -1,			/* Tested */
	'tags'			=> '',			/* Tested */
	'tags_and'		=> '',			/* Tested */
	'icon'			=>  1,			/* Tested */
	'logged_users'  => -1,			/* Tested */
	'include'		=> '',			/* Tested */
	'exclude'		=> '',			/* Tested */
	'nofollow'		=>  0,			/* Tested for standard size*/
	'target'		=>  0,			/* Tested */
	'exclude_thumbnail' => 1
);

 $EGA_FIELDS_ORDER_LABEL = array(
	'id'			=> 'ID',
	'caption' 		=> 'Caption',
	'date'			=> 'Date',
	'description' 	=> 'Description',
	'filename'		=> 'File name',
	'menu_order'	=> 'Menu order',
	'title' 		=> 'Title',
	'type'			=> 'Type'
);

 $EGA_FIELDS_ORDER_KEY = array(
	'id'			=> 'ID',
	'caption' 		=> 'post_excerpt',
	'date'			=> 'post_date',
	'description' 	=> 'post_content',
	'filename'		=> 'post_name',
	'menu_order'	=> 'menu_order',
	'title' 		=> 'post_title',
	'type'			=> 'post_mime_type'
);


if (! class_exists('EG_Attachments_Common')) {

	Class EG_Attachments_Common {

		static function parse_template($content) {
			$template = FALSE;
			// IMPORTANT: option s is mandatory to manage the carriage returns
			preg_match_all('/\[before\](.*)\[\/before\](.*)\[loop\](.+)\[\/loop\](.*)\[after\](.*)\[\/after\]/is',
							$content,
							$matches);
			if (sizeof($matches) > 4) {
				$template = array(
					'before'	=> $matches[1][0],
					'loop'		=> $matches[3][0],
					'after'		=> $matches[5][0]);
			}
			return ($template);
		} // End of parse_template

		static function get_templates($options, $type='all', $title_only=TRUE) {

			$template_list = (EG_PLUGIN_ENABLE_CACHE ? get_transient('eg-attachments-templates') : FALSE);
			if (FALSE === $template_list) {
				$results = get_posts(array(
							'post_status' 	=> 'publish',
							'post_type'		=> EGA_TEMPLATE_POST_TYPE,
							'orderby' 		=> 'title',
							'order' 		=> 'ASC',
							'numberposts' 	=> -1 /*,
							$include_exclude=> $options['standard_templates'] */
						)
					);
				$template_list = array( 'standard' => array(), 'custom' => array() );
				$std_tmpl = explode(',', $options['standard_templates']);
				if ($results) {
					foreach ($results as $template) {
						if (in_array($template->ID, $std_tmpl)) {
							$template_list['standard'][$template->post_name] = $template;
						}
						else {
							$template_list['custom'][$template->post_name] = $template;
						}
					}
				}
				if (EG_PLUGIN_ENABLE_CACHE)
					set_transient('eg-attachments-templates', $template_list, EGA_TEMPLATE_CACHE_EXPIRATION);

			} // End of no data in cache
			$returned_list = FALSE;
			if (FALSE !== $template_list)

				if ('all' == $type)
					$returned_list = array_merge($template_list['standard'], $template_list['custom']);
				else
					$returned_list = $template_list[$type];

				if ($title_only) {
					foreach ($returned_list as $key => $value) {
						$returned_list[$key] = esc_html($value->post_title);
				} // End of foreach
			}
			return ($returned_list);
		} // End of get_templates

		static function get_shortcode_defaults($options) {
			global $EGA_SHORTCODE_DEFAULTS;

			$values = array(
				'force_saveas'		=> $options['force_saveas'],
				'logged_users'		=> $options['logged_users_only'],
				'login_url' 		=> $options['login_url'],
				'nofollow' 			=> $options['nofollow'],
				'target' 			=> $options['target_blank'],
				'exclude_thumbnail'	=> $options['exclude_thumbnail'],
			);
			return wp_parse_args($values, $EGA_SHORTCODE_DEFAULTS);

		} // End of get_shortcode_defaults

	} // End of class

} // End of class_exists
?>