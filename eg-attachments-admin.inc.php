<?php

if (! class_exists('EG_Forms_105')) {
	require('lib/eg-forms.inc.php');
}

if (! class_exists('EG_Attachments_Admin')) {

	/**
	 * Class EG_Attachments
	 *
	 * Implement a shortcode to display the list of attachments in a post.
	 *
	 * @package EG-Attachments
	 */
	Class EG_Attachments_Admin extends EG_Plugin_109 {

		function plugins_loaded() {

			parent::plugins_loaded();

			// Add options page
			$this->add_page('options', 					/* page type: post, page, option, tool 	*/
							'EG-Attachments Options',	/* Page title 							*/
							'EG-Attachments',			/* Menu title 							*/
							'manage_options', 			/* Access level / capability			*/
							'ega_options',				/* file 								*/
							'options_page');			/* function								*/

		} // End of plugins_loaded

		/**
		 * Implement init action
		 *
		 * Add filter, hooks or action.
		 *
		 * @package EG-Attachments
		 * @param none
		 * @return none
		 */
		function init() {

			parent::init();

			// Clear cache when adding or delete attachment
			add_action('add_attachment',    array(&$this, 'clean_cache' ));
			add_action('delete_attachment', array(&$this, 'clean_cache' ));

		} /* End of init */

		/**
		 * add_form
		 *
		 * create form
		 *
		 * @package EG-Attachments
		 *
		 * @param none
		 * @return none
		 */
		function add_form() {

			$form = new EG_Forms_105('EG-Attachments Options', '', '', $this->textdomain, '', 'icon-options-general', 'ega_options', 'mailto:'.get_option('admin_email'));

			$id_section = $form->add_section('Auto shortcode');
			$id_group   = $form->add_group($id_section, 'Activation');
			$form->add_field($id_section, $id_group, 'select', 'Activation', 'shortcode_auto', '', '', 'With this option, you can automaticaly add the list of attachments in your blog, without using shortcode', '', 'regular', array( 0 => 'Not activated', 2 => 'At the end'));

			$id_group   = $form->add_group($id_section, 'Where');
			$form->add_field($id_section, $id_group, 'select', 'Where', 'shortcode_auto_where', '', '', 'Lists of attachments can be displayed everywhere posts are displayed, or only when a single post or a single page is displayed', '', 'regular', array( 'all' => 'in all pages', 'post' => 'Only for posts and pages'));
			$id_group   = $form->add_group($id_section, 'Auto Shortcode Options');
			$form->add_field($id_section, $id_group, 'text', 'Title of the list: ',  'shortcode_auto_title');
			$form->add_field($id_section, $id_group, 'text', 'HTML Tag for title: ', 'shortcode_auto_title_tag');
			$form->add_field($id_section, $id_group, 'select', 'List size: ',        'shortcode_auto_size',     '', '', '', '', 'regular', array( 'small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'));
			$form->add_field($id_section, $id_group, 'select', 'Document type: ',    'shortcode_auto_doc_type', '', '', '', '', 'regular', array( 'all' => 'All', 'document' => 'Documents', 'image' => 'Images'));
			$form->add_field($id_section, $id_group, 'select', 'Document label: ',   'shortcode_auto_label',    '', '', 'Choose the field that will be displayed as title of documents', '', 'regular', array( 'filename' => 'File name', 'doctitle' => 'Document title'));
			$form->add_field($id_section, $id_group, 'select', 'Order by: ',         'shortcode_auto_orderby',  '', '', '', '', 'regular', array( 'ID' => 'ID', '0' => 'Title', 'date' => 'Date', 'mime' => 'Mime type'));
			$form->add_field($id_section, $id_group, 'select', 'Sort Order: ',       'shortcode_auto_order',    '', '', '', '', 'regular', array( 'ASC' => 'Ascending', 'DESC' => 'Descending'));
			$form->add_field($id_section, $id_group, 'checkbox', 'Fields: ',         'shortcode_auto_fields',   'Which fields do you want to display (large and medium size only)?', '', '', '', 'regular', array( 'caption' => 'Caption', 'description' => 'Description'));
			$form->add_field($id_section, $id_group, 'checkbox', 'Check the box to display icons',  'shortcode_auto_icon', 'Display icons: ', '', '', '', 'regular');
			$form->add_field($id_section, $id_group, 'checkbox', 'Do you want that auto shortcode options become the default options for the TinyMCE EG-Attachments Editor?', 'shortcode_auto_default_opts', 'Default options? ', '', '', '', 'regular' );

			$id_section = $form->add_section('General behavior of shortcodes');
			$id_group   = $form->add_group($id_section, '"Save As" activation', "In normal mode, when you click on the attachments' links, according their mime type, documents are displayed, or a dialog box appears to choose 'run with' or 'Save As'. By activating the following option, the dialog box will appear for all cases.");
			$form->add_field($id_section, $id_group, 'checkbox', 'Force "Save As" when users click on the attachments', 'force_saveas');
			$id_group   = $form->add_group($id_section, 'Attachments access', '', 'This option sets the default behavior of all shortcodes and auto-shortcode. You can change the behavior of specific shortcodes by adding <code>logged_users</code> parameter.');
			$form->add_field($id_section, $id_group, 'checkbox', 'Restrict access to the attachments to logged users only!', 'logged_users_only');
			/*  */
			$form->add_field($id_section, $id_group, 'text', 'Url to login or register page:', 'login_url');

			$id_section = $form->add_section('Uninstall options', '', 'Be careful: these actions cannot be cancelled. All plugin\'s options will be deleted while plugin uninstallation.');
			$id_group   = $form->add_group($id_section, 'Options');
			$form->add_field($id_section, $id_group, 'checkbox', 'Delete options during uninstallation.', 'uninstall_del_options');

			$form->add_button('submit', 'eg_attach_options_submit', 'Save changes');
			$form->add_button('reset',  'eg_attach_options_reset',  'Cancel changes');
			$form->add_button('submit', 'eg_attach_options_reset',  'Reset to defaults', 'reset_to_defaults');

			return ($form);
		}

		/**
		 * install_upgrade
		 *
		 * 
		 *
		 * @package EG-Attachments
		 *
		 * @param none
		 * @return none
		 */
		function install_upgrade() {
		
			$previous_options = parent::install_upgrade();
/*
			if ($previous_options !== FALSE && version_compare($previous_options['version'], '1.4.3', '<')) {
				if (isset($previous_options['uninstall_del_option'])) {
					$this->options['uninstall_del_options'] = $previous_options['uninstall_del_option'];
					if (isset($this->options['uninstall_del_option'])) 
						unset($this->options['uninstall_del_option']);
					update_option($this->options_entry, $this->options);
				}
			}
*/
		} // End of install_upgrade
		
		/**
		 * options_page
		 *
		 * Display the options page
		 *
		 * @param 	none
		 * @return 	none
		 */
		function options_page() {

			$form = $this->add_form();
			$results = $form->get_form_values($this->options, $this->default_options, $this->options_entry);
			if ($results)
				$this->options = $results;

			$form->display_form($this->options);			
		} /* options_page */


		/**
		 * clean_cache
		 *
		 * Clear cache containing lists of attachments per post
		 *
		 * @package EG-Attachments
		 * @param int	$id	(unused) id of post
		 * @return none
		 */
		function clean_cache($id) {
			wp_cache_delete( 'attachments', 'eg-attachments' );
		} // End of clean_cache

	} /* End of Class */
} /* End of if class_exists */

$eg_attach_admin = new EG_Attachments_Admin('EG-Attachments',
											EG_ATTACH_VERSION ,
											EG_ATTACH_COREFILE,
											EG_ATTACH_OPTIONS_ENTRY,
											$EG_ATTACH_DEFAULT_OPTIONS);

$eg_attach_admin->set_textdomain('eg-attachments');
$eg_attach_admin->set_wp_versions('2.5', FALSE, '2.6', FALSE);
$eg_attach_admin->add_tinymce_button( 'EGAttachments', 'tinymce');
$eg_attach_admin->load();

?>