<?php

if (! class_exists('EG_Attachments_Widget')) {

	define('EGA_WIDGET_ID', 'eg-attach' );

	Class EG_Attachments_Widget extends EG_Widget_210 {

		function __construct() {
			global $EGA_SHORTCODE_DEFAULTS;
			global $EGA_FIELDS_ORDER_LABEL;

			$plugin_options = get_option(EGA_OPTIONS_ENTRY);

			// widget settings
			$widget_ops = array('classname' => 'widget_attachments', 
								'description' => __('Display attachments of the current post', EGA_TEXTDOMAIN )
							);

			// create the widget
			parent::__construct(EGA_WIDGET_ID, 'EG-Attachment Widget', $widget_ops);

			$templates_list = EG_Attachments_Common::get_templates($plugin_options, 'all');
			
			$this->fields = array(
				'title'				=> array( 'type'  => 'text',	'label'  => 'Title'),
				'template'			=> array( 'type'  => 'select',	'label'  => 'Template',
					'list' => $templates_list),
				'doctype'			=> array( 'type'  => 'select',	'label'  => 'Document type',
					'list' => array( 'all'   => 'All', 	'document' => 'Documents', 'image' => 'Images')),
				'exclude_thumbnail'	=> array( 'type'  => 'checkbox', 'label'  => 'Exclude thumbnail',
					'list' => array( 'Check to exclude the feature image from the attachments list')),
				'orderby'			=> array( 'type'  => 'select',   'label' => 'Order by',
					'list' => $EGA_FIELDS_ORDER_LABEL), /*array_intersect_key($EG_ATTACH_FIELDS_TITLE, $EG_ATTACH_FIELDS_ORDER_KEY)) */
				'order'				=> array( 'type'  => 'select',   'label'  => 'Order',
					'list' => array( 'ASC'   => 'Ascending', 'DESC'  => 'Descending')),
				'force_saveas'		=> array( 'type'  => 'checkbox', 'label'  => '"Save As" activation',
					'list' => array('Force "Save As" when users click on the attachments')),
				'limit'				=> array( 'type'  => 'text', 'label'      => 'Number of documents to display'),
				'nofollow'	  		=> array( 'type'  => 'checkbox', 'label'  => '&laquo;Nofollow&raquo; attribute',
					'list' => array( 'Check if you want to automatically add <code>rel="nofollow"</code> to attachment links' )),
				'target'	  		=> array( 'type'  => 'checkbox', 'label'  => '&laquo;Target&raquo; attribute',
					'list'	=> array( 'Check if you want to automatically add <code>target="_blank"</code> to attachment links' )),
				'logged_users' 		 => array( 'type'  => 'select', 'label' => 'Attachments access',
					'list' => array( -1 => 'Use default parameter', 0 => 'All users', 1 => 'Only logged users'))
			);

			if ($plugin_options['tags_assignment']) {
				$fields['tags'] = array( 'type'  => 'select', 'label' => 'Tags', 'list' => eg_attach_get_tags_select('array'));
			}

			$this->default_options = EG_Attachments_Common::get_shortcode_defaults($plugin_options);
			list($this->default_options['orderby'], $this->default_options['order']) = explode(' ', $this->default_options['orderby']);
			$this->default_options['title'] = __('Attachments', EGA_TEXTDOMAIN);

			$this->textdomain = EGA_TEXTDOMAIN;
		} // End of constructor


		function widget($args, $instance) {
			global $eg_attach_public;
			global $EGA_SHORTCODE_DEFAULTS;

			$output = '';
			if (is_singular() && isset($eg_attach_public)) {			
				/* --- Extract parameters --- */
				extract($args);

				$values = wp_parse_args( (array) $instance, $this->default_options );

				// Put title to '', before calling the shortcode
				$widget_title 		= $values['title']; 
				$values['title']    = '';
				$values['orderby'] .= ' '.$values['order'];
				unset($values['order']);

				$shortcode = '[attachments';
				foreach ($values as $key => $value) {
					if ($value != $EGA_SHORTCODE_DEFAULTS[$key]) 
						$shortcode .= ' '.$key.'='.(is_numeric($value) ? $value : '"'.$value.'"');
				} // End of foreach
				$shortcode .= ']';
// eg_plugin_error_log('EG-Attachments Widgets', 'do shortcode', $shortcode);	
				$output = do_shortcode($shortcode);

				if ($output != '') {
					$title = apply_filters('widget_title', $widget_title, $values, $this->id_base);

					echo $before_widget.
						('' != $title ? $before_title.__($title, $this->textdomain).$after_title:'').
						$output.
						$after_widget;
				} // End of $output != ''

			} // End of (is_singular && $eg_attach)
		} // End of widget

	} // End of class

} // End of class_exists EG_Attach_Widget

function eg_attachments_widgets_init() {
	register_widget('EG_Attachments_Widget');
}
add_action('init', 'eg_attachments_widgets_init', 1);

?>