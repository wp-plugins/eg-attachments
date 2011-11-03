<?php

if (eg_detect_page( 'ega_options')) {
	require(dirname(EGA_COREFILE).'/lib/eg-forms.inc.php');
}

//if (! class_exists('EG_Cache_100')) {
//	require_once('lib/eg-tools.inc.php');
//}

if (! class_exists('EG_Attachments_Admin')) {

	/**
	 * Class EG_Attachments
	 *
	 * Implement a shortcode to display the list of attachments in a post.
	 *
	 * @package EG-Attachments
	 */
	Class EG_Attachments_Admin extends EG_Plugin_124 {

//		var $cache;
		var $edit_posts_pages = array('post.php', 'post-new.php', 'page.php', 'page-new.php');

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
			$previous_version = ($previous_options === FALSE ? FALSE : $previous_options['version']);

			if ($previous_version !== FALSE) { // Is it a new installation

				if (version_compare($previous_version, '1.4.3', '<') && isset($this->options['uninstall_del_option'])) {
					$this->options['uninstall_del_options'] = $previous_options['uninstall_del_option'];
					unset($this->options['uninstall_del_option']);

					update_option($this->options_entry, $this->options);
				} // End of version older than 1.4.3

				if ( isset($this->options['shortcode_auto_format'])) {

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
					update_option($this->options_entry, $this->options);
				} // End of version older than 1.7.3

				if (version_compare($previous_version, '1.9.2', '<')) {
					if ($this->options['shortcode_auto_where'] == 'post')
						$this->options['shortcode_auto_where'] = array( 'post', 'page');
					else
						$this->options['shortcode_auto_where'] = array( 'home', 'post', 'page', 'index');

					update_option($this->options_entry, $this->options);

				} // End of version older than 1.9.2

			} // End of not a new installation

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

				if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
					// Table created successfully => save a flag.
					$this->options['clicks_table'] = 1;
					update_option($this->options_entry, $this->options);
				}
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

			$this->add_page( array(
					'id' 				=> 'ega_options',
					'display_callback'	=> 'options_page',
					'option_link'		=> TRUE)
			);

			if ($this->options['stats_enable']) {
				$this->add_page( array(
					'id' 				=> 'ega_stats',
					'type'				=> 'tools',
					'page_title' 		=> 'EG-Attachments Statistics',
					'menu_title'		=> 'EG-Attachments Stats',
					'access_level'		=> 'edit_posts',
					'display_callback'	=> 'stats_page')
				);
			}

			parent::admin_menu();

			if (class_exists('EG_Form_211')) {
				require($this->path.'inc/eg-attachments-settings.inc.php');
			}

			// if ($this->options['use_metabox'] && function_exists( 'add_meta_box' ) &&
			if ($this->options['use_metabox'] && function_exists( 'add_meta_box' ) &&
				in_array($pagenow, $this->edit_posts_pages) ) {

				// Add metabox for posts
				add_meta_box( 'eg-attach-metabox', __( 'EG-Attachments', $this->textdomain ),
							array(&$this, 'display_metabox'), 'post', 'normal', 'high' );

				// Add metabox for pages
				add_meta_box( 'eg-attach-metabox', __( 'EG-Attachments', $this->textdomain ),
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
			$attachment_list  = get_posts( array(	'post_parent' 	=> $post->ID,
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
								'<th>'.__('ID', $this->textdomain).'</th>'.
								'<th>'.__('File Name', $this->textdomain).'</th>'.
								'<th>'.__('Type', $this->textdomain).'</th>'.
								'<th>'.__('Size', $this->textdomain).'</th>'.
								'<th>'.__('Date', $this->textdomain).'</th>'.
							'</tr>';
				foreach ($attachment_list as $attachment) {
					$file_path = get_attached_file($attachment->ID);
					$file_type = wp_check_filetype($file_path);
					$docsize = @filesize($file_path);
					$size_value = explode(' ',size_format($docsize, 0)); // WP function found in file wp-includes/functions.php
					$string .= '<tr>'.
								'<td>'.$attachment->ID.'</td>'.
								'<td>'.wp_html_excerpt($attachment->post_title, 40).'</td>'.
								'<td>'.$file_type['ext'] /* str_replace('vnd.','',str_replace('application/','',$attachment->post_mime_type)) */.'</td>'.
								'<td>'.(sizeof($size_value)<2?'':$size_value[0].' '.__($size_value[1], $this->textdomain)).'</td>'.
								'<td>'.mysql2date(get_option('date_format'),$attachment->post_date, TRUE).'</td>'.
							'</tr>';
				}
				$string .= '</table>';

				echo $string;
			}
?>
			</div>
<?php
		} // End of display_metabox

		function display_sidebar() {
			global $locale;

			$string = sprintf('<ul>'.
							  '<li><a href="http://wordpress.org/extend/plugins/eg-attachments/">%s</a></li>'.
							  '<li><a href="http://wordpress.org/extend/plugins/eg-attachments/faq">%s</a></li>'.
							  '<li><a href="http://wordpress.org/tags/eg-attachments">%s</a></li>'.
							  '<li><a href="http://wordpress.org/extend/plugins/eg-attachments/changelog/">%s</a></li>'.
							  '</ul>',
							__('Plugin\'s homepage', 		$this->textdomain),
							__('Frequently Asked Questions',$this->textdomain),
							__('Support forum', 			$this->textdomain),
							__('Last changes', 				$this->textdomain));
			$this->display_box('links', 'Links', $string);

			$string = '<p>'.__('This plugin required and requires many hours of work. If you use the plugin, and like it, feel free to show your appreciation to the author.', $this->textdomain).'</p>';
			$string .= '<form action="https://www.paypal.com/cgi-bin/webscr" method="post">'.
						'<input type="hidden" name="cmd" value="_donations">'.
						'<input type="hidden" name="business" value="CPCKAJFRB5NNA">'.
						'<input type="hidden" name="lc" value="'.($locale=='fr_FR'?'FR':'US').'">'.
						'<input type="hidden" name="item_number" value="eg-attachments">'.
						'<input type="hidden" name="currency_code" value="EUR">'.
						'<input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_LG.gif:NonHosted">'.
						'<input type="image" src="https://www.paypalobjects.com/'.($locale=='fr_FR'?'fr_FR':'en_US').'/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="'.__('PayPal - The safer, easier way to pay online!', $this->textdomain).'">'.
						'<img alt="" border="0" src="https://www.paypalobjects.com/'.($locale=='fr_FR'?'fr_FR':'en_US').'/i/scr/pixel.gif" width="1" height="1">'.
						'</form>';
			$this->display_box('paypal', 'Donate', $string);
		} // End of display_sidebar

		/**
		 * options_page
		 *
		 * Display the options page
		 *
		 * @param 	none
		 * @return 	none
		 */
		function options_page() {
			if ($this->options_form)
				$this->options_form->display_page($this->options);
		} // End of options_page

		/**
		 * clean_cache
		 *
		 * Clear cache containing lists of attachments per post
		 *
		 * @package EG-Attachments
		 * @param int	$id	(unused) id of post
		 * @return none
		 */
//		function clean_cache($id) {
//			wp_cache_delete( 'attachments', 'eg-attachments' );
//		} // End of clean_cache

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
?>
			<div class="wrap">'.
			<?php screen_icon(); ?>
				<h2><?php _e('EG-Attachments Statistics', $this->textdomain); ?></h2>
<?php
			if (! isset($_GET['id'])) {
				$this->stats_display_global();
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

			$month_str = array( 1 => 'Jan', 2 => 'Feb', 3 => 'Mar',  4 => 'Apr',  5 => 'May',  6 => 'Jun',
								7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec');

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

				$sql = 'SELECT YEAR(click_date) as year, MONTH(click_date) as month, SUM(clicks_number) as clicks_total'.
					' FROM '.$wpdb->prefix.'eg_attachments_clicks '.
					' WHERE attach_id='.$id.
					' GROUP BY DATE_FORMAT(click_date,\'%Y-%m\')';

				$current_year = date('Y');
				$fields = array( ($current_year-1) => 0, $current_year => 0, 'Q1' => 0, 'Q2' => 0, 'Q3' => 0, 'Q4' => 0,
						1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0);

				$results = $wpdb->get_results($sql);

				$max_number = 0;
				foreach ($results as $result) {

					if (isset($fields[$result->year])) $fields[$result->year] += $result->clicks_total;
					if ($result->year == $current_year) {
						$fields[$result->month] += $result->clicks_total;
						$fields[$quarter_list[$result->month]] += $result->clicks_total;
					}
				}
				$max_number = max($fields);

				echo '<h3>'.sprintf(__('History of "%s" ', $this->textdomain), $attachment->post_title).'</h3>'.
					'<table class="eg-attach-stats-details">'.
					'<tr>'.
					'<td colspan="18">'.__('Clicks', $this->textdomain).'</td>'.
					'</tr>';

				echo '<tr>';
				foreach ($fields as $key => $field) {
					if (is_numeric($key) && $key < 13) echo '<td>'.__( $month_str[$key], $this->textdomain).'</td>';
					else echo '<td>'.$key.'</td>';
				}
				echo '</tr>'.
					'<tr class="histogram">';
				foreach ($fields as $key => $field) {
					echo '<td><div title="'.$field.'" style="height: '.intval($field*$max_height/$max_number).'px;">&nbsp;</div></td>';
				}

				echo '</tr><tr>';
				foreach ($fields as $key => $field) {
					if (is_numeric($key) && $key < 13) echo '<td>'.__( $month_str[$key], $this->textdomain).'</td>';
					else echo '<td>'.$key.'</td>';
				}
				echo '</table>';

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
		function stats_display_global($limit=10) {
			global $wpdb;

			// Purge statistics table (2 years of retention)
			$sql = 'DELETE FROM '.$wpdb->prefix.'eg_attachments_clicks '.
					'WHERE click_date<"'.date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, date('Y')-1)).'"';
			$status = $wpdb->query($sql);

			//$global_stats = $this->cache->get('eg_attachment_clicks_total');
			//if (! $global_stats) {

				$sql = 'SELECT attach_id, attach_title as title, SUM(clicks_number) as total,'.
					' 0 as last_month, 0 as current_month, 0 as last_week, 0 as current_week, 0 as yesterday, 0 as today'.
					' FROM '.$wpdb->prefix.'eg_attachments_clicks '.
					' GROUP BY attach_id'.
					' ORDER BY total DESC';

				$global_stats = $wpdb->get_results($sql, 'OBJECT_K');
				if ($global_stats) {

					list($current_day, $current_week, $current_month, $current_year) = explode(' ',date('dmY WY mY Y'));
					$yesterday = date('dmY', strtotime('-1 day'));
					$previous_week = date('WY', strtotime('-1 week'));
					$previous_month = date('WY', strtotime('-1 month'));
					$date_limit = date('Y-m-d 00:00:00', mktime(0, 0, 0, 1, 1, $current_year-1));

					$sql = 'SELECT attach_id,YEAR(click_date) as year, MONTH(click_date) as month, DAY(click_date) as day, SUM(clicks_number) as clicks_total'.
						' FROM '.$wpdb->prefix.'eg_attachments_clicks '.
						' WHERE click_date >= "'.$date_limit.'"'.
						' GROUP BY attach_id, click_date';

					$results = $wpdb->get_results($sql);
					if ($results) {
						foreach ($results as $result) {
							$id    = $result->attach_id;
							if (isset($global_stats[$id])) {

								$week  = date('WY', mktime(0,0,0, $result->month, $result->day, $result->year));
								$month = date('mY', mktime(0,0,0, $result->month, 1, $result->year));
								$day   = date('dmY', mktime(0,0,0, $result->month, $result->day, $result->year));

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
							} // isset $global_stats[$id]
						} // foreach
					} // if $results
					unset($results);
					//$this->cache->set('eg_attachment_clicks_total', $global_stats);
				//} // if $results (general query)
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

$eg_attach_admin = new EG_Attachments_Admin('EG-Attachments',
											EGA_VERSION ,
											EGA_COREFILE,
											EGA_TEXTDOMAIN,
											EGA_OPTIONS_ENTRY,
											$EG_ATTACH_DEFAULT_OPTIONS);

$eg_attach_admin->set_wp_versions('3.0', '3.3-beta1');
$eg_attach_admin->add_tinymce_button( 'EGAttachments', 'inc/tinymce', 'eg_attach_plugin.js');
$eg_attach_admin->set_stylesheets('css/eg-attachments-admin.css');
if (EGA_DEBUG_MODE)
	$eg_attach_admin->set_debug_mode(TRUE, 'debug.log');

$eg_attach_admin->load();

?>