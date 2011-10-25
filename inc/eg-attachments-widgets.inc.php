<?php

if (!class_exists('EG_Attach_Widget')) {

	class EG_Attach_Widget extends EG_Widget_203 {

		function EG_Attach_Widget() {
			global $EG_ATTACHMENT_SHORTCODE_DEFAULTS;
			global $EG_ATTACH_FIELDS_TITLE, $EG_ATTACH_FIELDS_ORDER_KEY, $EG_ATTACH_DEFAULT_FIELDS;

			$widget_ops = array('classname' => 'widget_attachments', 'description' => 'Display attachments of the current post' );
			$this->WP_Widget('eg_attach', 'EG-Attachment Widget', $widget_ops);

			$plugin_options = get_option(EGA_OPTIONS_ENTRY);

			$fields = array(
					'title'			=> array( 'type'  => 'text',	'label'  => 'Title'),
					'size'			=> array( 'type'  => 'select',	'label'  => 'Size',
						'list' 		=> array( 'small' => 'Small',	'medium' => 'Medium', 'large' => 'Large', 'custom' => 'Custom')),
					'doctype'		=> array( 'type'  => 'select',	'label'  => 'Document type',
						'list' 		=> array( 'all'   => 'All', 	'document' => 'Documents', 'image' => 'Images')),
					'label'			=> array( 'type'  => 'select',	'label' => 'Document label',
						'list' 		=> array( 'filename' => 'File name', 'doctitle' => 'Document title')),
					'fields'		=> array( 'type'  => 'checkbox', 'label' => 'Custom fields:',
						'list' 		=> $EG_ATTACH_FIELDS_TITLE),
					'orderby'		=> array( 'type'  => 'select',   'label' => 'Order by',
						'list' 		=> array_intersect_key($EG_ATTACH_FIELDS_TITLE, $EG_ATTACH_FIELDS_ORDER_KEY)),
					'order'			=> array( 'type'  => 'select',   'label'  => 'Order',
						'list'		=> array( 'ASC'   => 'Ascending', 'DESC'  => 'Descending')),
					'sep1'			=> array( 'type'  => 'separator'),
					'icon'			=> array( 'type'  => 'checkbox', 'label'  => 'Display icon'),
					'force_saveas'	=> array( 'type'  => 'checkbox', 'label'  => '"Save As" activation'),
					'limit'			=> array( 'type'  => 'text', 'label'      => 'Number of documents to display'),
					'nofollow'	    => array( 'type'  => 'checkbox', 'label'  => '&laquo;Nofollow&raquo; attribute'),
					'logged_users'  => array( 'type'  => 'select', 'label' => 'Attachments access',
						'list' => array( -1 => 'Use default parameter', 0 => 'All users', 1 => 'Only logged users')),
					'sep2'			=> array( 'type'  => 'separator'),
					'format_pre'	=> array( 'type'  => 'textarea',	'label' => 'Custom format, before list'),
					'format'		=> array( 'type'  => 'textarea',	'label' => 'Custom list format'),
					'format_post'	=> array( 'type'  => 'textarea',	'label' => 'Custom format, after list')
			);

			$default_values = array_intersect_key($EG_ATTACHMENT_SHORTCODE_DEFAULTS, $fields);
			$default_values['format_pre']   = isset($plugin_options['custom_format_pre'])  ? $plugin_options['custom_format_pre']  : '' ;
			$default_values['format']       = isset($plugin_options['custom_format'])      ? $plugin_options['custom_format']      :'' ;
			$default_values['format_post']  = isset($plugin_options['custom_format_post']) ? $plugin_options['custom_format_post'] : '' ;

			list($default_values['orderby'], $default_values['order']) = explode(' ', $EG_ATTACHMENT_SHORTCODE_DEFAULTS['orderby']);
			$default_values['title'] = 'Attachments';

			$this->set_options(EGA_TEXTDOMAIN, EGA_COREFILE, 0 );
			$this->set_form($fields, $default_values, FALSE );

		} // End of constructor

		function widget($args, $instance) {
			global $eg_attach;

			extract($args, EXTR_SKIP);
			$values = wp_parse_args( (array) $instance, $this->default_values );

			$widget_title 		= $values['title'];
			$values['title']    = '';
			$values['orderby'] .= ' '.$values['order'];
			if (isset($values['fields'])) $values['fields'] = implode(',', $values['fields']);
			else $values['fields'] = '';

			$output = '';
			if (is_singular() && isset($eg_attach)) {
				global $post;
				if ('private' == get_post_field('post_status', $post->ID) && !is_user_logged_in()) {
					$output = __('This post is private. You must be a user of the site, and logged in, to display this file.', $this->textdomain);
				}
				else if (post_password_required($post->ID)) {
					$output = __('This post is password protected. Please go to the site, and enter the password required to display the document', $this->textdomain);
				}
				else {
					$output = $eg_attach->get_attachments($values);
				}
			} // End of (is_singular && $eg_attach)
			if ($output != '') {
				echo $before_widget.
					($widget_title!= ''?$before_title.__($widget_title, $this->textdomain).$after_title:'').
					$output.
					$after_widget;
			}

		} // End of function widget

	} // End of EG_Attach_Widget

} // End of class_exists EG_Attach_Widget

function eg_attachments_widgets_init() {

	register_widget('EG_Attach_Widget');
}
add_action('init', 'eg_attachments_widgets_init', 1);


?>