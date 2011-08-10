<?php

if (!class_exists('EG_Attach_Widget')) {

	class EG_Attach_Widget extends EG_Widget_201 {
	
		function EG_Attach_Widget() {
			global $EG_ATTACHMENT_SHORTCODE_DEFAULTS;
		
			$widget_ops = array('classname' => 'widget_attachments', 'description' => 'Display attachments of the current post' );
			$this->WP_Widget('eg_attach', 'EG-Attachment Widget', $widget_ops);

			$fields = array(
					'title'		=> array( 'type'  	=> 'text',	'label' => 'Title'),
					'size'		=> array( 'type'  	=> 'select',	'label' => 'Size',
						'list' 	=> array( 'small' 	=> 'Small',	'medium' => 'Medium', 'large' => 'Large')),
					'doctype'	=> array( 'type'  	=> 'select',	'label' => 'Document type',
						'list' 	=> array( 'all' 	=> 'All', 	'document' => 'Documents', 'image' => 'Images')),
					'label'		=> array( 'type'  	=> 'select',	'label' => 'Document label',
						'list' 	=> array( 'filename' => 'File name', 'doctitle' => 'Document title')),
					'fields'	=> array( 'type'  => 'checkbox', 'label' => 'Fields',
						'list' 	=> array( 'caption' => 'Caption', 'description' => 'Description')),
					'orderby'	=> array( 'type'  => 'radio',   'label' => 'Order by',
						'list' 	=> array( 'ID' => 'ID', 'title' => 'Title', 'date' => 'Date', 'mime' => 'Mime type')),
					'order'		=> array( 'type'  => 'radio',   'label' => 'Order',
						'list'	=> array( 'ASC' => 'Ascending', 'DESC' => 'Descending')),
					'icon'		=> array( 'type'  => 'checkbox', 'label' => 'Display icon')
			);

			$default_values  = $EG_ATTACHMENT_SHORTCODE_DEFAULTS;
			$default_orderby = $default_values['orderby'];
			$default_values['title'] = 'Attachments';
			list($default_values['orderby'], $default_values['order']) = explode(' ', $default_orderby);			

			$this->set_options(EG_ATTACH_TEXTDOMAIN, EG_ATTACH_COREFILE, 0 );
			$this->set_form($fields, $default_values, FALSE );
	
		} // End of constructor
		
		function widget($args, $instance) {
			global $eg_attach;
		
			extract($args, EXTR_SKIP);
			$values = wp_parse_args( (array) $instance, $this->default_values );
			
			$widget_title = $values['title'];
			$values['title']    = '';
			$values['orderby'] .= ' '.$values['order'];
			if (isset($values['fields'])) $values['fields']   = implode(',', $values['fields']);
			else $values['fields'] = '';

			$output = '';
			if ((is_single() || is_page()) && isset($eg_attach)) {
				if ($eg_attach->shortcode_is_visible()) 
					$output = $eg_attach->get_attachments($values);
				else
					$output = __('Cannot display attachments. Current post or page is protected.',$this->textdomain);
			} // End of is_single

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