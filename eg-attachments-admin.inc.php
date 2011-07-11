<?php

if (! class_exists('EG_Forms_111')) {
	require('lib/eg-forms.inc.php');
}

if (! class_exists('EG_Cache_100')) {
	require_once('lib/eg-tools.inc.php');
}

if (! class_exists('EG_Attachments_Admin')) {

	/**
	 * Class EG_Attachments
	 *
	 * Implement a shortcode to display the list of attachments in a post.
	 *
	 * @package EG-Attachments
	 */
	Class EG_Attachments_Admin extends EG_Plugin_116 {

		var $cache;
		var $edit_posts_pages = array('post.php', 'post-new.php', 'page.php', 'page-new.php');

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

			$this->cache = new EG_Cache_100('eg-attachments', 900, dirname(__FILE__).'/tmp');

			// Clear cache when adding or delete attachment
			add_action('add_attachment',    array(&$this, 'clean_cache' ));
			add_action('delete_attachment', array(&$this, 'clean_cache' ));

			// Add options page
			$this->add_page('options', 						/* page type: post, page, option, tool 	*/
			 				'EG-Attachments Options',		/* Page title 							*/
							'EG-Attachments',				/* Menu title 							*/
							'manage_options', 				/* Access level / capability			*/
							'ega_options',					/* file 								*/
							'options_page');				/* function								*/

			if ($this->options['stats_enable']) {

				// Add click stats page
				$this->add_page('tools', 					/* page type: post, page, option, tool 	*/
							'EG-Attachments Statistics',	/* Page title 							*/
							'EG-Attachments Stats',			/* Menu title 							*/
							'edit_posts', 					/* Access level / capability			*/
							'ega_stats',					/* file 								*/
							'stats_page');					/* function								*/
			}

		} /* End of init */

		/**
		 * install_upgrade
		 *
		 * Install or upgrade options and database
		 *
		 * @package EG-Attachments
		 *
		 * @param none
		 * @return none
		 */
		function install_upgrade() {
			global $wpdb;

			$previous_options = parent::install_upgrade();

			$previous_version = $previous_options['version'];
			if (version_compare($previous_version, '1.4.3', '<') && isset($this->options['uninstall_del_option'])) {
				$this->options['uninstall_del_options'] = $previous_options['uninstall_del_option'];
				unset($this->options['uninstall_del_option']);

				upgrade_option($this->options_entry, $this->options);
			} // End of version older than 1.4.3

			if ( isset($this->options['shortcode_auto_format'])) {
				/* version_compare($previous_version, '1.7.3.1', '<') && */
				$changed_options = array(
								'shortcode_auto_format_pre'  => 'custom_format_pre',
								'shortcode_auto_format'      => 'custom_format',
								'shortcode_auto_format_post' => 'custom_format_post');
				foreach ($changed_options as $old_option => $new_option) {
					if (isset($this->options[$old_option])) {
						$this->options[$new_option] = $this->options[$old_option];
						unset($this->options[$old_option]);
					}
				}
				upgrade_option($this->options_entry, $this->options);
			} // End of version older than 1.7.3

			$table_name = $wpdb->prefix . "eg_attachments_clicks";
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

				$sql = "CREATE TABLE " . $table_name . " (
						click_id bigint(20) NOT NULL auto_increment,
						click_date datetime NOT NULL default '0000-00-00 00:00:00',
						attach_id bigint(20) unsigned,
						attach_title text NOT NULL,
						post_id bigint(20) unsigned,
						post_title text NOT NULL,
						clicks_number int(10) NOT NULL,
						UNIQUE KEY click_id (click_id),
						KEY date_attach_post (click_date, attach_id, post_id),
						KEY attach_date (attach_id, click_date),
						KEY click_date (click_date)
				);";

				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);
			}
			if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
				// Table created successfully => save a flag.
				$this->options['clicks_table'] = 1;
				update_option($this->options_entry, $this->options);
			}

		} // End of install_upgrade

		/**
		 * admin_menu
		 *
		 * Add metabox action
		 *
		 * @param	none
		 * @return 	none
		 */
		function admin_menu() {
			global $pagenow;

			parent::admin_menu();

			// if ($this->options['use_metabox'] && function_exists( 'add_meta_box' ) &&
			if ($this->options['use_metabox'] && function_exists( 'add_meta_box' ) &&
				in_array($pagenow, $this->edit_posts_pages) ) {

				// Add metabox for posts
				add_meta_box( 'eg-attach-metabox', __( 'EG-Attachments', $this->textdomain ),
							array(&$this, 'display_metabox'), 'post', 'normal', 'high' );

				// Add metabox for pages
				add_meta_box( 'eg-series-metabox', __( 'EG-Series', $this->textdomain ),
							array(&$this, 'display_metabox'), 'page', 'normal', 'high' );

			}
		} // End of admin_menu

		/**
		 * display_metabox
		 *
		 * Display attachments meta box
		 *
		 * @param	object	$post		post or page currently edited
		 * @param	array	$args		other arguments
		 * @return 	none
		 */
		function display_metabox($post, $args) {
			global $post;

?>
			<div id="egattach-stuff">
<?php

			$attachment_list  = get_posts( array('post_parent' => $post->ID,
													'numberposts'	=> -1,
													'post_type'		=> 'attachment',
												)
											);
			if ($attachment_list === FALSE && sizeof($attachment_list)==0) {
				_e('No document attached to this post/page', $this->textdomain);
			}
			else {
				$string = '<p>'.__('Attachments available for this post/page', $this->textdomain).'</p>'.
						'<table class="eg-attach-list">'.
							'<tr>'.
								'<th>'.__('File Name', $this->textdomain).'</th>'.
								'<th>'.__('Type', $this->textdomain).'</th>'.
								'<th>'.__('Size', $this->textdomain).'</th>'.
								'<th>'.__('Date', $this->textdomain).'</th>'.
							'</tr>';
				foreach ($attachment_list as $attachment) {
					$file_path = get_attached_file($attachment->ID);
					$file_type = wp_check_filetype($file_path);
					$docsize = @filesize($file_path);
					$size_value = split(' ',size_format($docsize, 0)); // WP function found in file wp-includes/functions.php
					$string .= '<tr>'.
								'<td>'.wp_html_excerpt($attachment->post_title, 40).'</td>'.
								'<td>'.$file_type['ext'] /* str_replace('vnd.','',str_replace('application/','',$attachment->post_mime_type)) */.'</td>'.
								'<td>'.(sizeof($size_value)<2?'':$size_value[0].' '.__($size_value[1], $this->textdomain)).'</td>'.
								'<td>'.mysql2date('j M Y',$attachment->post_date, true).'</td>'.
							'</tr>';
				}
				$string .= '</table>';

				echo $string;
			}
?>
			</div>
<?php
		} // End of display_metabox

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
			global $EG_ATTACH_DEFAULT_OPTIONS;
			global $EG_ATTACH_DEFAULT_FIELDS;
			global $EG_ATTACH_FIELDS_TITLE;
			global $EG_ATTACH_FIELDS_ORDER_KEY;

			$form = new EG_Forms_111('EG-Attachments Options', '', '', $this->textdomain, '', 'icon-options-general', 'ega_options', 'mailto:'.get_option('admin_email'));

			$id_section = $form->add_section('Auto shortcode');
			$id_group   = $form->add_group($id_section, 'Activation');
			$form->add_field($id_section, $id_group, 'select', 'Activation', 'shortcode_auto', '', '', 'With this option, you can automaticaly add the list of attachments in your blog, without using shortcode', '', 'regular', array( 0 => 'Not activated', 2 => 'At the end', 3 => 'Before the excerpt', 4 => 'Between excerpt and content'));

			$id_group   = $form->add_group($id_section, 'Auto / Manual');
			$form->add_field($id_section, $id_group, 'checkbox', 'Disable auto-shortcode when a manual shortcode is detected', 'shortcode_auto_exclusive', '', '', 'If this option is activated, auto shortcode won\'t be generated if a manual shortcode is detected in the post being displayed', '', 'regular');

			$id_group   = $form->add_group($id_section, 'Where');
			$form->add_field($id_section, $id_group, 'select', 'Where', 'shortcode_auto_where', '', '', 'Lists of attachments can be displayed everywhere posts are displayed, or only when a single post or a single page is displayed', '', 'regular', array( 'all' => 'in all pages', 'post' => 'Only for posts and pages'));

			$id_group   = $form->add_group($id_section, 'Auto Shortcode Options');
			$form->add_field($id_section, $id_group, 'text', 'Title of the list: ',  'shortcode_auto_title');
			$form->add_field($id_section, $id_group, 'text', 'HTML Tag for title: ', 'shortcode_auto_title_tag');
			$form->add_field($id_section, $id_group, 'select', 'List format: ',      'shortcode_auto_size',     '', '', '', '', 'regular', array( 'small' => 'Small', 'medium' => 'Medium', 'large' => 'Large', 'custom' => 'Custom'));

			$form->add_field($id_section, $id_group, 'select', 'Document type: ',    'shortcode_auto_doc_type', '', '', '', '', 'regular', array( 'all' => 'All', 'document' => 'Documents', 'image' => 'Images'));
			
			$sort_key_list = array_intersect_key($EG_ATTACH_FIELDS_TITLE, $EG_ATTACH_FIELDS_ORDER_KEY);
			
			$form->add_field($id_section, $id_group, 'select', 'Order by: ',         'shortcode_auto_orderby',  '', '', '', '', 'regular', $sort_key_list);
			$form->add_field($id_section, $id_group, 'select', 'Sort Order: ',       'shortcode_auto_order',    '', '', '', '', 'regular', array( 'ASC' => 'Ascending', 'DESC' => 'Descending'));
			$form->add_field($id_section, $id_group, 'checkbox', 'Check the box to display icons',  'shortcode_auto_icon', 'Display icons: ', '', '', '', 'regular');
			$form->add_field($id_section, $id_group, 'checkbox', 'Do you want that auto shortcode options become the default options for the TinyMCE EG-Attachments Editor?', 'shortcode_auto_default_opts', 'Default options? ', '', '', '', 'regular' );

			$default_fields = __('The default list is: ', $this->textdomain).'<br />';
			foreach ($EG_ATTACH_DEFAULT_FIELDS as $list_size => $fields_list) {
				$default_fields .= '<strong>'.__(ucfirst(strtolower($list_size)),$this->textdomain).'</strong> '.__(' list', $this->textdomain).': ';
				foreach ($fields_list as $value) {
					$default_fields .= __($EG_ATTACH_FIELDS_TITLE[$value], $this->textdomain).', ';
				}
				$default_fields .= '<br />';
			}

			$id_group   = $form->add_group($id_section, 'Fields to be displayed');
			$form->add_field($id_section, $id_group, 'select', 'Document label: ',   'shortcode_auto_label',    '', '', 'Choose the field that will be displayed as title of documents', '', 'regular', array( 'filename' => 'File name', 'doctitle' => 'Document title'));
			$form->add_field($id_section, $id_group, 'checkbox', 'Do you want to display default fields?', 'shortcode_auto_fields_def', '', '', $default_fields, '', 'regular');

			$field_values = $this->options['shortcode_auto_fields'];
			for ($i=sizeof($field_values)+1; $i<=sizeof($EG_ATTACH_FIELDS_TITLE); $i++) {
				$field_values[$i]= '0';
			}
			$fields_grid = array( 'header' => array('Position', 'Field') );
			for ($i=1; $i<=sizeof($field_values); $i++) {
				$fields_grid['list'][] = array( 'value' => $i, 'select' => array_merge(array(' '),$EG_ATTACH_FIELDS_TITLE));
			}

			$form->add_field($id_section, $id_group, 'grid select', 'or display the following fields', 'shortcode_auto_fields', '', '', '', '', 'regular', $fields_grid);

			$id_section = $form->add_section('Custom format','You can define your own display format, using <strong>custom</strong>. Parameters of this section will be applied only if you use <em>Custom</em> as display format for auto-shortcode (fifth option of this page), or if you specify <em>size=custom</em> in the shortcodes in your post.<br />Keywords you can use are listed below.');
			$id_group   = $form->add_group($id_section, 'Format', '','Available keywords:<br /><table class="eg-attach-custom-format"><tr><td><strong>%LINK%</strong></td><td>Full link of document (such as &lt;a href="..."&gt;...), </td></tr><tr><td><strong>%URL%</strong></td><td>url of document (permalink),</td></tr><tr><td><strong>%GUID%</strong></td><td>direct link to document as stored in the WP database,</td></tr><tr><td><strong>%ICONURL%</strong></td><td>URL of icon,</td></tr><tr><td><strong>%TITLE%</strong></td><td>Title of the document,</td></tr><tr><td><strong>%CAPTION%</strong></td><td>caption of the document,</td></tr><tr><td><strong>%DESCRIPTION%</strong></td><td>description of the document,</td></tr><tr><td><strong>%FILENAME%</strong></td><td>Name of the attached file,</td></tr><tr><td><strong>%FILESIZE%</strong></td><td>Size of the attached document,</td></tr><tr><td><strong>%ATTID%</strong></td><td>ID of the attachment.</td></tr></table>');
			$form->add_field($id_section, $id_group, 'textarea', 'Custom format, before list: ', 'custom_format_pre' );
			$form->add_field($id_section, $id_group, 'textarea', 'Custom list format: ',         'custom_format'     );
			$form->add_field($id_section, $id_group, 'textarea', 'Custom format, after list: ',  'custom_format_post');

			$id_section = $form->add_section('General behavior of shortcodes');

			$id_group   = $form->add_group($id_section, '&laquo;Nofollow&raquo; attribute');
			$form->add_field($id_section, $id_group, 'checkbox', 'Check if you want to automatically add rel="nofollow" to attachment links', 'nofollow', '', '', '', '', 'regular' );

			$id_group   = $form->add_group($id_section, '"Save As" activation', "In normal mode, when you click on the attachments' links, according their mime type, documents are displayed, or a dialog box appears to choose 'run with' or 'Save As'. By activating the following option, the dialog box will appear for all cases.");
			$form->add_field($id_section, $id_group, 'checkbox', 'Force "Save As" when users click on the attachments', 'force_saveas');
			$id_group   = $form->add_group($id_section, 'Attachments access', '', 'This option sets the default behavior of all shortcodes and auto-shortcode. You can change the behavior of specific shortcodes by adding <code>logged_users</code> parameter.');
			$form->add_field($id_section, $id_group, 'checkbox', 'Restrict access to the attachments to logged users only!', 'logged_users_only');
			/*  */
			$form->add_field($id_section, $id_group, 'text', 'Url to login or register page:', 'login_url');

			$id_section = $form->add_section('Click counter');
			$id_group   = $form->add_group($id_section, 'Activate');
			$form->add_field($id_section, $id_group, 'checkbox', 'Record all clicks occuring in the listed attachements.', 'stats_enable');
			$id_group   = $form->add_group($id_section, 'Exclude IP');
			$form->add_field($id_section, $id_group, 'text', 'List of IP address you want to exclude', 'stats_ip_exclude');

			$id_section = $form->add_section('Styles', '', '');
			$id_group   = $form->add_group($id_section, 'Styles');
			$form->add_field($id_section, $id_group, 'checkbox', 'Check if you want to use the plugin stylesheet file, uncheck if you want to use your own styles, or include styles on the theme stylesheet.', 'load_css', '', '', '', '', 'regular' );

			$id_section = $form->add_section('Administration interface', '', '');
			$id_group   = $form->add_group($id_section, 'Post editor page');
			$form->add_field($id_section, $id_group, 'checkbox', 'Show metabox to display list of attachments of the current post/page', 'use_metabox', '', '', '', '', 'regular' );


			$id_section = $form->add_section('Uninstall options', '', 'Be careful: these actions cannot be cancelled. All plugin\'s options will be deleted while plugin uninstallation.');
			$id_group   = $form->add_group($id_section, 'Options');
			$form->add_field($id_section, $id_group, 'checkbox', 'Delete options during uninstallation.', 'uninstall_del_options');

			$form->add_button('submit', 'eg_attach_options_submit', 'Save changes');
			$form->add_button('reset',  'eg_attach_options_reset',  'Cancel changes');
			$form->add_button('submit', 'eg_attach_options_reset',  'Reset to defaults', 'reset_to_defaults');

			return ($form);
		}

		/**
		 * options_page
		 *
		 * Display the options page
		 *
		 * @param 	none
		 * @return 	none
		 */
		function options_page() {
			global $EG_ATTACH_FIELDS_TITLE;

			$form = $this->add_form();
			$results = $form->get_form_values($this->options, $this->default_options);
			if ($results) {
				// Clean  shortcode_auto_fields by removing
				$tempo = array();
				foreach ($results['shortcode_auto_fields'] as $value) {
					if ($value != "0") $tempo[sizeof($tempo)+1] = $value;
				}

				for ($i=sizeof($tempo)+1; $i<=sizeof($EG_ATTACH_FIELDS_TITLE); $i++) {
					$tempo[$i]= '0';
				}
				$results['shortcode_auto_fields'] = $tempo;
				$this->options = $results;
				update_option($this->options_entry, $results);
			}
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

		/**
		 * stats_page
		 *
		 * Display and manage statistics page
		 *
		 * @package EG-Attachments
		 * @param none
		 * @return none
		 */
		function stats_page() {

			echo '<div class="wrap">'.
				'<div id="icon-tools" class="icon32"></div>'.
				'<h2>'.__('EG-Attachments Statistics', $this->textdomain).'</h2>';

			if (! isset($_GET['id'])) {
				$this->stats_display_global($limit);
			}
			else {
				if (is_numeric($_GET['id'])) {
					$this->stats_display_details($_GET['id']);
				}
			}
			echo '</div>';
		} // Stats_page

		/**
		 * stats_display_breadcrumb
		 *
		 * Display breadcrumb on statistics page
		 *
		 * @package EG-Attachments
		 * @param none
		 * @return none
		 */
		function stats_display_breadcrumb($level2 = FALSE) {

			if (! $level2)
				$list[] = '<strong>'.__('Global', $this->textdomain).'</strong>';
			else {
				$list[] = '<a href="'.admin_url('tools.php?page=ega_stats').'">'.__('Global', $this->textdomain).'</a>';
				$list[] = '<strong>'.sprintf(__('Details of %s', $this->textdomain),current($level2)).'</strong>';
			}
			echo '<ul class="subsubsub"><li>'.
				implode('</li> &gt; <li>', $list).
				'</li></ul><div class="clear"/>';
		} // End of stats_display_breadcrumb

		/**
		 * stats_display_top_numbers
		 *
		 * Display top clicked attached files (main page)
		 *
		 * @package EG-Attachments
		 * @param int	$Limit	number of attachments to display
		 * @return none
		 */
		function stats_display_top_numbers($all) {

			$top_numbers_list = array(
					'10'  => __('Top 10', $this->textdomain),
					'25'  => __('Top 25', $this->textdomain),
					'all' => __('All',    $this->textdomain)
			);

			$limit = 10;
			if (isset($_GET['limit']) && array_key_exists($_GET['limit'], $top_numbers_list)) {
				$limit = $_GET['limit'];
			}

			$top_numbers_filter = array();
			foreach ($top_numbers_list as $key => $value) {
				if ($key == $limit)
					$top_numbers_filter[] = '<li>'.$value.'</li>';
				else
					$top_numbers_filter[] = '<li><a href="'.admin_url('tools.php?page=ega_stats&limit='.$key).'">'.__($value, $this->textdomain).'</a><li>';
			}
			echo '<ul class="subsubsub">'.implode(' | ', $top_numbers_filter).'</ul>';

			if ($limit == 'all' ) return ($all);
			else return ($limit);

		} // End of stats_display_top_numbers

		/**
		 * stats_display_details
		 *
		 * Display detailed statistic of a specific attachment
		 *
		 * @package EG-Attachments
		 * @param int	$id		id of the attachment
		 * @return none
		 */
		function stats_display_details($id) {
			global $wpdb;

			$quarter_list = array(
				 1 => 'Q1',  2 => 'Q1',  3 => 'Q1',
				 4 => 'Q2',  5 => 'Q2',  6 => 'Q2',
				 7 => 'Q3',  8 => 'Q3',  9 => 'Q3',
				10 => 'Q4', 11 => 'Q4', 12 => 'Q4'
			);
			$max_height = 200;

			$attachment = get_post($id);
			if (! $attachment) {
				echo 'Error msg';
			}
			else {
				$this->stats_display_breadcrumb(array( $id => $attachment->post_title));

				$sql = 'SELECT DATE_FORMAT(click_date,\'%Y-%m\') as month, SUM(clicks_number) as clicks_total'.
					' FROM '.$wpdb->prefix.'eg_attachments_clicks '.
					' WHERE attach_id='.$id.
					' GROUP BY DATE_FORMAT(click_date,\'%Y-%m\')';

				$current_year    = date('Y');
				$previous_year   = $current_year -1;
				$field_list = array( $previous_year, $current_year, 'Q1', 'Q2', 'Q3', 'Q4',
									 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
				foreach ( $field_list as $field ) {
					$details[$field] = 0;
				}

				$results = $wpdb->get_results($sql);

				$max_number = 0;
				foreach ($results as $result) {

					list($year, $month) = split('-', $result->month);
					$month = intval($month);

					$details[$year] += $result->clicks_total;
					if ($year == $current_year) {
						$details[$month] += $result->clicks_total;
						$details[$quarter_list[$month]] += $result->clicks_total;
					}
				}
				$max_number = max($details);

				echo '<h3>'.sprintf(__('History of "%s" ', $this->textdomain), $attachment->post_title).'</h3>'.
					'<table class="eg-attach-stats-details">'.
					'<tr>'.
					'<td colspan="18">'.__('Clicks', $this->textdomain).'</td>'.
					'</tr>';

				echo '<tr>';
				foreach ($field_list as $field) {
					echo '<td>'.$details[$field].'</td>';
				}
				echo '</tr>'.
					'<tr class="histogram">';
				foreach ($field_list as $field) {
					echo '<td><div title="'.$field.': '.$details[$field].'" style="height: '.intval($details[$field]*$max_height/$max_number).'px;">&nbsp;</div></td>';
				}

				echo '</tr>';

				echo '<tr>'.
					'<td>'.(date('Y')-1).'</td>'.
					'<td>'.date('Y').'</td>'.
					'<td>'.__('Q1',  $this->textdomain).'</td>'.
					'<td>'.__('Q2',  $this->textdomain).'</td>'.
					'<td>'.__('Q3',  $this->textdomain).'</td>'.
					'<td>'.__('Q4',  $this->textdomain).'</td>'.
					'<td>'.__('Jan', $this->textdomain).'</td>'.
					'<td>'.__('Feb', $this->textdomain).'</td>'.
					'<td>'.__('Mar', $this->textdomain).'</td>'.
					'<td>'.__('Apr', $this->textdomain).'</td>'.
					'<td>'.__('May', $this->textdomain).'</td>'.
					'<td>'.__('Jun', $this->textdomain).'</td>'.
					'<td>'.__('Jul', $this->textdomain).'</td>'.
					'<td>'.__('Aug', $this->textdomain).'</td>'.
					'<td>'.__('Sep', $this->textdomain).'</td>'.
					'<td>'.__('Oct', $this->textdomain).'</td>'.
					'<td>'.__('Nov', $this->textdomain).'</td>'.
					'<td>'.__('Dec', $this->textdomain).'</td>'.
					'</tr>'.
					'</table>';

				$sql = 'SELECT post_id,post_title, SUM(clicks_number) as clicks_total'.
					' FROM '.$wpdb->prefix.'eg_attachments_clicks '.
					' WHERE attach_id='.$id.
					' GROUP BY post_id'.
					' ORDER BY clicks_total DESC';

				echo '<h3>'.__('Posts', $this->textdomain).'</h3>'.
					'<table class="wide widefat eg-attach-stats">'.
					'<thead>'.
					'<tr>'.
					'<th>'.__('Posts', $this->textdomain).'</th>'.
					'<th>'.__('Clicks', $this->textdomain).'</th>'.
					'<th>'.__('%', $this->textdomain).'</th>'.
					'</tr>'.
					'</thead>'.
					'<tbody>';
				$results = $wpdb->get_results($sql);
				$total   = 0;
				foreach ($results as $result) {
					$total += $result->clicks_total;
				}
				foreach ($results as $result) {
					echo '<tr>'.
						'<th>'.$result->post_title.'</th>'.
						'<td>'.$result->clicks_total.'</td>'.
						'<td>'.intval( (100 * $result->clicks_total) / $total ).'</td>'.
						'</tr>';
				}
				echo '<tr>'.
					'<th>'.__('Total', $this->textdomain).'</th>'.
					'<td>'.$total.'</td>'.
					'<td>&nbsp;</td>'.
					'</tr>'.
					'</tbody>'.
					'</table>';
			}
		} // End of stats_display_details

		/**
		 * stats_display_global
		 *
		 * Display global statistics for all attachments
		 * @package EG-Attachments
		 * @param int	$id		id of the attachment
		 * @return none
		 */
		function stats_display_global($limit) {
			global $wpdb;

			// Purge statistics table (2 years of retention)
			$sql = 'DELETE FROM '.$wpdb->prefix.'eg_attachments_clicks '.
					'WHERE click_date<'.date('Y-m-d H:i:s', mktime(0, 0, 0, 12, 1, date('Y')-2));
			$status = $wpdb->query($sql);

			$global_stats = $this->cache->get('eg_attachment_clicks_total');
			if (! $global_stats) {

				$sql = 'SELECT attach_id,attach_title, SUM(clicks_number) as clicks_total'.
					' FROM '.$wpdb->prefix.'eg_attachments_clicks '.
					' GROUP BY attach_id'.
					' ORDER BY clicks_total DESC';

				$results = $wpdb->get_results($sql);
				if ($results) {
					foreach ($results as $result) {
						$id = $result->attach_id;
						$global_stats[$id]->title         = $result->attach_title;
						$global_stats[$id]->total         = $result->clicks_total;
						$global_stats[$id]->last_month    = 0;
						$global_stats[$id]->current_month = 0;
						$global_stats[$id]->last_week     = 0;
						$global_stats[$id]->current_week  = 0;
						$global_stats[$id]->yesterday     = 0;
						$global_stats[$id]->today         = 0;
					}
					unset($results);

					list($current_day, $current_week, $current_month, $current_year) = explode(' ',date('d W m Y'));

					if ($current_month == 1) {
						$date_limit = date('Y-m-d 00:00:00', mktime(0, 0, 0, 12, 1, $current_year-1));
						$previous_month = '12'.$current_year;
					}
					else {
						$date_limit = date('Y-m-d 00:00:00', mktime(0, 0, 0, $current_month-1, 1, $current_year));
						$previous_month = date('m', mktime(0, 0, 0, $current_month-1, 1, $current_year)).$current_year;
					}

					if ($current_week == 1) $previous_week = date('W', mktime(0, 0, 0, 12, 31, $current_year-1)).$current_year;
					else $previous_week = ($current_week - 1).$current_year;

					$yesterday = date('d', mktime(0, 0, 0, $current_month, $current_day, $current_year) - 24*3600).
									$current_month.
									$current_year;

					$current_month = $current_month.$current_year;
					$current_week  = $current_week.$current_year;
					$current_day   = $current_day.$current_month;

					$sql = 'SELECT attach_id,click_date, SUM(clicks_number) as clicks_total'.
						' FROM '.$wpdb->prefix.'eg_attachments_clicks '.
						' WHERE click_date > "'.$date_limit.'"'.
						' GROUP BY attach_id, click_date';

					$results = $wpdb->get_results($sql);
					foreach ($results as $result) {
						$id = $result->attach_id;

						list($year, $month, $day, $click_time) = split('[- ]', $result->click_date);
						$week = date('W', mktime(0,0,0, $month, $day, $year)).$year;

						$month = $month.$year;
						$day   = $day.$month;

						if (isset($global_stats[$id])) {
							if ($current_month == $month)
								$global_stats[$id]->current_month += $result->clicks_total;
							elseif ($previous_month == $month)
								$global_stats[$id]->last_month += $result->clicks_total;

							if ($current_week == $week)
								$global_stats[$id]->current_week += $result->clicks_total;
							elseif ($previous_week == $week)
								$global_stats[$id]->last_week += $result->clicks_total;

							if ($current_day == $day)
								$global_stats[$id]->today += $result->clicks_total;
							elseif ($yesterday == $day)
								$global_stats[$id]->yesterday += $result->clicks_total;
						}
					} // if $results (date query)
					unset($results);
					$this->cache->set('eg_attachment_clicks_total', $global_stats);
				} // if $results (general query)
			}

			$this->stats_display_breadcrumb();

			if ($global_stats) $limit = $this->stats_display_top_numbers(sizeof($global_stats));
			echo '<table class="wide widefat eg-attach-stats">'.
				 '<thead>'.
				 '<tr>'.
				 '<th>'.__('Attachments', 		 $this->textdomain).'</th>'.
				 '<th>'.__('All<br />time', 	 $this->textdomain).'</th>'.
				 '<th>'.__('Last<br />month', 	 $this->textdomain).'</th>'.
				 '<th>'.__('Current<br />month', $this->textdomain).'</th>'.
				 '<th>'.__('Last<br />week', 	 $this->textdomain).'</th>'.
				 '<th>'.__('Current<br />week',  $this->textdomain).'</th>'.
				 '<th>'.__('Yesterday', 		 $this->textdomain).'</th>'.
				 '<th>'.__('Today', 			 $this->textdomain).'</th>'.
				 '</tr>'.
				 '</thead>'.
				 '<tbody>';

			if (! $global_stats) {
				echo '<tr><td colspan="8">'.__('No statistics available yet', $this->textdomain).'</td></tr>';
			}
			else {
				$index = 0;
				foreach ($global_stats as $id => $values) {
					echo '<tr>'.
						'<th><a href="'.admin_url('tools.php?page=ega_stats&id='.$id).'">'.$values->title.'</a></th>'.
						'<td>'.$values->total.'</td>'.
						'<td>'.$values->last_month.'</td>'.
						'<td>'.$values->current_month.'</td>'.
						'<td>'.$values->last_week.'</td>'.
						'<td>'.$values->current_week.'</td>'.
						'<td>'.$values->yesterday.'</td>'.
						'<td>'.$values->today.'</td>'.
						'</tr>';
					if ($index++ > $limit) break;
				}
			} // global_stats exists
			echo '</tbody></table>';
		} // End of stats_display_globals

	} /* End of Class */
} /* End of if class_exists */

function eg_attachments_uninstall() {
	$options = get_option(EG_ATTACH_OPTIONS_ENTRY);
	if ( isset($options) && $options['uninstall_del_options']) {
		delete_option(EG_ATTACH_OPTIONS_ENTRY);
	}
} // End of eg_attachments_uninstall


$eg_attach_admin = new EG_Attachments_Admin('EG-Attachments',
											EG_ATTACH_VERSION ,
											EG_ATTACH_COREFILE,
											EG_ATTACH_OPTIONS_ENTRY,
											$EG_ATTACH_DEFAULT_OPTIONS);

$eg_attach_admin->set_textdomain('eg-attachments');
$eg_attach_admin->set_wp_versions('2.9', FALSE, '2.9', FALSE);
$eg_attach_admin->add_tinymce_button( 'EGAttachments', 'tinymce');
$eg_attach_admin->set_stylesheets(FALSE, 'eg-attachments-admin.css');

register_uninstall_hook (EG_ATTACH_COREFILE, 'eg_attachments_uninstall' );
$eg_attach_admin->load();

?>