<?php
/*
Package Name: EG-Plugin
Package URI:
Description: Class for WordPress plugins
Version: 1.2.2
Author: Emmanuel GEORJON
Author URI: http://www.emmanuelgeorjon.com/
*/

/*
    Copyright 2009-2011 Emmanuel GEORJON  (email : blog@emmanuelgeorjon.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if (!function_exists('eg_detect_page')) {
	function eg_detect_page($pages_list) {
		if (!is_array($pages_list)) $pages_list = array($pages_list);
		return ( (isset($_GET['page']) && in_array($_GET['page'], $pages_list)) || (isset($_POST['action']) && $_POST['action']=='update'));
	}
}

if (!class_exists('EG_Plugin_122')) {

	/**
	  * Class EG_Plugin
	  *
	  * Provide some functions to create a WordPress plugin
	  *
	 */
	Class EG_Plugin_122 {

		var $name;
		var $version;
		var $path;
		var $url;
		var $corefile;
		var $options_entry;
		var $options			= NULL;
		var $default_options;
		var $stylesheet 		= '';
		var $debug_mode, $debug_file;
		var $wp_version_min 	= '3.0';
		var $wp_version_max;
		var $multi_site			=TRUE;
		var $requirements_error_msg = '';
		var $update_notice 		= '';
		var $options_page_id 	= '';
		var $pages 				= array();
		var $hooks 				= array();
		var $tinyMCE_buttons 	= array();

		/**
		  * Class contructor for PHP 4 compatibility
		  *
		  * @package EG-Plugins
		  * @return object
		  *
		  */
		function EG_Plugin_122($plugin_name, $version, $core_file, $textdomain, $options_entry, $default_options=FALSE) {

			register_shutdown_function(array(&$this, '__destruct'));
			$this->__construct($plugin_name, $version, $core_file, $textdomain, $options_entry, $default_options);
		} // End of EG_Plugin_121

		/**
		  * Class contructor
		  * Define the plugin url and path. Declare action INIT and HEAD.
		  *
		  * @package EG-Plugins
		  * @return object
		  */
		function __construct($plugin_name, $version, $core_file, $textdomain, $options_entry, $default_options=FALSE) {
			$this->name     		= $plugin_name;
			$this->version  		= $version;
			$this->textdomain		= $textdomain;
			$this->options_entry   	= $options_entry;
			$this->default_options 	= $default_options;

			/* Define the plugin path */
			$this->path 	= trailingslashit(str_replace('\\', '/', WP_PLUGIN_DIR.'/'.dirname(plugin_basename($core_file))));
			$this->url  	= plugin_dir_url($core_file);
			$this->corefile = $core_file;
		} // End of __construct

		/**
		 * Class destructor
		 *
		 * @package EG-Plugins
		 * @return boolean true
		 */
		function __destruct() {
			// Nothing
		} // End of __destruct

		/**
		 * plugins_loaded
		 *
		 * Call by plugins_loaded WP action
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return none
		 */
		function plugins_loaded () {

			/* --- Check if WP version is correct --- */
			//$this->check_requirements();

			/* --- Get Plugin options --- */
			if (! $this->options)
				$this->options = get_option($this->options_entry);;
		} // End of plugins_loaded

		/**
		 * init
		 *
		 * Perform init
		 *
		 * @package EG-Plugins
		 *
		 * @param	none
		 * @return	none
		 */
		function init() {

			if ($this->textdomain != '')
				load_plugin_textdomain( $this->textdomain, FALSE, dirname( plugin_basename( $this->corefile ) ).'/lang');

			$this->load_styles();
			// Add only in Rich Editor mode
			if ( isset($this->tinyMCE_buttons) &&
				 get_user_option('rich_editing') == 'true' ) {
			// && current_user_can('edit_posts') && current_user_can('edit_pages') )  {

				add_filter('tiny_mce_version', 		array(&$this, 'tiny_mce_version'   ));
				add_filter('mce_external_plugins', 	array(&$this, 'add_tinymce_plugin' ));
				add_filter('mce_buttons', 			array(&$this, 'register_button'    ));
			}
		} // End of init

		function desactivation() {
		} // End of desactivation

		/**
		 * upgrade
		 *
		 * Create or update plugin environment (options, database, files ...)
		 *
		 * @package EG-Plugins
		 * @param 	none
		 * @return  none
		 */
		 /*
			To use this function:
				function insta_upgrade() {
					$current_version = parent::install_upgrade();

					put here your upgrade or install features
				}
		 */
		function install_upgrade() {

			if (! $this->options)
				$this->options = get_option($this->options_entry);

			if ($this->options === FALSE) {

				$previous_options = FALSE;
				// Create option from the defaults
				if ($this->default_options !== FALSE) {
					$this->options = $this->default_options;
				}
				$this->options['version'] = $this->version;
				add_option($this->options_entry, $this->options);
			} // End of options empty (first install)
			else {

				$previous_options = $this->options;
				$current_version = (isset($this->options['version'])? $this->options['version']: '0.0.0');

				// Plugin previously installed. Check the version and update options
				if (version_compare($current_version, $this->version, '<')) {
					if ($this->default_options === FALSE) {
						$new_options = $this->options;
					}
					else {
						$new_options = array();
						foreach ($this->default_options as $key => $value) {
							if (isset($this->options[$key])) $new_options[$key] = $this->options[$key];
							else $new_options[$key] = $value;
						}
					}
					$new_options['version'] = $this->version;
					update_option($this->options_entry, $new_options);
					$this->options = $new_options;
				} // End of version compare
			} // End of options not empty (update)
			return ($previous_options);

		} // End of install_upgrade

		function admin_init() {
		} // End of admin_init

		/**
		 * admin_head
		 *
		 * Implement Head action
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return none
		 */
		function admin_head() {
		} // End of admin_head


		/**
		 * admin_footer
		 *
		 * Implement footer admin  actions
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return none
		 */
		function admin_footer() {
		} // End of admin_footer

		/**
		 * Implement Head action
		 *
		 * Add links to the style sheet
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return none
		 */
		function head() {
		} // End of head

		/**
		 * Implement Footer action
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return none
		 */
		function footer() {
		} // End of footer

		function wp_logout() {
		} // End of wp_logout

		/**
		 * Load
		 *
		 * @package EG-Plugins
		 * @return none
		 */
		function load() {

			add_action('plugins_loaded', array(&$this, 'plugins_loaded') );
			add_action('init',           array(&$this, 'init')           );
			add_action('wp_logout',      array(&$this, 'wp_logout')      );

			if (is_admin()) {

				register_deactivation_hook( $this->corefile, array(&$this, 'desactivation') );
				register_activation_hook( $this->corefile, array(&$this, 'install_upgrade') );
				add_action('in_plugin_update_message-' . basename($this->corefile),
							array( &$this, 'plugin_update_notice') );

				add_action('admin_menu',   array( &$this, 'admin_menu')   );
				add_action('admin_init',   array( &$this, 'admin_init')   );
				add_action('admin_header', array( &$this, 'admin_head')   );
				add_action('admin_footer', array( &$this, 'admin_footer') );
			}
			else {
				add_action('wp_head',   array( &$this, 'head')  );
				add_action('wp_footer', array( &$this, 'footer'));
			}

		} // End of load

		/**
		 * set_stylesheet
		 *
		 * Set stylesheet for public and admin interface
		 *
		 * @package EG-Plugins
		 *
		 * @param	string	$stylesheet			stylesheet for the public side
		 * @param	string	$admin_stylesheet	stylesheet for the admin side
		 * @return none
		 */
		function set_stylesheets($stylesheet) {
			$this->stylesheet = $stylesheet;
		} // End of set_styleshhet

		/**
		 * load_styles
		 *
		 * Register plugin style sheets
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return none
		 */
		function load_styles() {

			if ($this->stylesheet != '') {

				$style_path = '';
				if (is_admin()) {
					if (@file_exists($this->path.$this->stylesheet)) {
						$style_path = $this->url.$this->stylesheet;
					}
				} // End of is_admin
				else {
					$load_css = isset($this->options['load_css']) ? $this->options['load_css'] : 1;
					if ($load_css) {
						if (@file_exists(TEMPLATEPATH.'/'.$this->stylesheet)) {
							$style_path = get_stylesheet_directory_uri().'/'.$this->stylesheet;
						}
						else {
							if (@file_exists($this->path.$this->stylesheet))
								$style_path = $this->url.$this->stylesheet;
						}
					} // End of load_css
				} // End of not is_admin
				if ($style_path != '') wp_enqueue_style( $this->name.'_stylesheet', $style_path);
			} // End of stylesheet defined
		} // End of load_styles

		/**
		 * set_debug_mode
		 *
		 * Set debug mode: display messages or not
		 *
		 * @package EG-Plugins
		 *
		 * @param	boolean	$debug_mode		TRUE to display message, FALSE to hide message
		 * @param	string	$debug_file		name of the file where store messages
		 * @return none
		 */
		function set_debug_mode($debug_mode = FALSE, $debug_file='') {
			$this->debug_mode = $debug_mode;
			if ($debug_file != '')
				$this->debug_file = $this->path.$debug_file;
		}

		/**
		 * display_debug_info
		 *
		 * @package EG-Plugin
		 *
		 * @param	string	$msg	message to display
		 * @param	mixed	$mixed	variable to display
		 * @return 	none
		 */
		function display_debug_info($mixed, $msg='') {

			$debug_info = debug_backtrace(FALSE);
			$output = date('d-M-Y H:i:s').' - '.$debug_info[1]['function'].' - '.$debug_info[2]['function'].($msg!=''?' - '.$msg:'').': ';
			if (! isset($mixed)) $output .= 'Not set';
			else {
				switch (gettype($mixed)) {
					case 'boolean':
						$output .= ($mixed === TRUE ? 'TRUE' : 'FALSE');
					break;

					case 'array':
					case 'object':
					case 'resource':
						$output .= var_export($mixed, TRUE);
					break;

					case 'NULL':
						$output .= 'NULL';
					break;

					default: $output .= $mixed;
				}
			}
			if ($this->debug_file == '') echo $output.'<br />';
			else file_put_contents(dirname(dirname(__FILE__)).'/debug.log', $output."\n", FILE_APPEND);

		} // End of display_debug_info

		/**
		 * set_wp_versions
		 *
		 * Set compliance parameters with WordPress and WordPress MU
		 *
		 * @package EG-Plugins
		 *
		 * @param	string	$wp_version_min		minimum version for WordPress
		 * @param	string	$wp_version_max	maximum version for WordPress
		 * @param	string	$wpmu_version_min	minimum version for WordPress MU
		 * @param	string	$wpmu_version_max	maximum version for WordPress MU
		 * @return none
		 */
		function set_wp_versions($wp_version_min, $wp_version_max='', $multi_site=TRUE) {
			$this->wp_version_min	= $wp_version_min;
			$this->wp_version_max	= $wp_version_max;
			$this->multi_site		= $multi_site;
		} // End of set_wp_versions

		/**
		  * get_requirements_msg
		  *
		  * Build requirements string
		  *
		  * @package 	EG-Plugins
		  * @param 		string	name of the software (for example: WP, WP MU, PHP, MySQL)
		  * @param	 	int		version minimum
		  * @param 		int 	version maximum
		  * @return		string	the message
		  */
		function get_requirements_msg($soft, $current='', $min=FALSE, $max=FALSE) {
			if (! $min) {
				$string = sprintf(__('%s cannot run with <strong>%s</strong>', $this->textdomain), $this->name, $soft).'.';
			}
			else {
				$string = sprintf(__('You are using %s %s.<br />%s requires %s %s or upper.', $this->textdomain), $soft, $current, $this->name, $soft, $min).($max?sprintf(__(' The plugin is tested with %s up to version %s', $this->textdomain), $soft, $max):'').'.';
			}
			return ($string);
		} // End of get_requirements_msg

		/**
		 * check_wp_requirements
		 *
		 * Check WordPress version required to run the plugin
		 *
		 * @package EG-Plugin
		 *
		 * @param boolean	display_msg		display error message or not
		 * @return boolean					TRUE if required WordPress version, FALSE if not
		 */
		function check_wp_requirements() {
			global $wp_version;

			$value = version_compare($wp_version, $this->wp_version_min, '>=');
			if ($this->wp_version_max) $value = $value && version_compare($wp_version, $this->wp_version_max, '<=');

			if (!$value)
				$this->requirements_error_msg .= $this->get_requirements_msg('WordPress', $wp_version, $this->wp_version_min, $this->wp_version_max);

			// TODO: is_multisite exists only since 3.0. Manage previous version
			if (is_multisite() && ! $this->multi_site) {
				$this->requirements_error_msg .= '<br />'.$this->get_requirements_msg('Multisite mode of WordPress');
				$value = FALSE;
			}
			return ($value);
		} // End of check_wp_requirements


		/**
		  * Check_requirements
		  *
		  * Check if the wordpress version meets the plugin requirements.
		  *
		  * @package EG-Plugins
		  * @param 		none
		  * @return 	none
		  */
		function check_requirements() {
			global $pagenow;

			$value = TRUE;
			if ( is_admin() && $pagenow == 'plugins.php' ) {

				$value = $this->check_wp_requirements();
				if (! $value) {
					echo '<div id="message" class="error fade"><p><strong>'.$this->requirements_error_msg.'</strong></p></div>';
				}
			}
			return ($value);
		} // End of check_requirements

		/**
		 * set_update_notice
		 *
		 *
		 *
		 * @package EG-Plugins
		 *
		 * @param	string	$msg	Message to add.
		 * @return none
		 */
		function set_update_notice($msg) {
			$this->update_notice = $msg;
		} // End of set_update_notice

		/**
		 * plugin_update_notice
		 *
		 * Display a specific message in the plugin update message.
		 *
		 * @package EG-Plugin
		 *
		 * @param	none
		 * @return 	none
		 */
		function plugin_update_notice() {
			if ($this->update_notice!= '')
				echo '<span class="spam">' .
						strip_tags( __($this->update_notice, $this->textdomain), '<br><a><b><i><span>' ) .
					'</span>';
		} // End of plugin_update_notice

		/**
		 * Filter_plugin_actions
		 *
		 * Add a "settings" link to access to the option page from the plugin list
		 *
		 * @package EG-Plugins
		 * @param string	$link	list of existings links
		 * @return none
		 */
		function filter_plugin_actions($links) {

			if ($this->options_page_id !='') {
				$settings_link = '<a href="'.admin_url('options-general.php?page='.$this->options_page_id).'">' . __('Settings') . '</a>';
				array_unshift( $links, $settings_link );
			}
			return $links;
		} // End of filter_plugin_actions


		/**
		 * display_box
		 *
		 * Display a metabox-like in admin interface.
		 *
		 * @package EG-Plugin
		 *
		 * @param	$id			string	id / name of the div containing box
		 * @param	$title		string	title of the box
		 * @param	$content	string	content of the box
		 * @return 	none
		 */
		function display_box($id, $title, $content) {
		?>
			<div id="<?php echo $id; ?>" class="postbox">
				<div class="handlediv" title="Click to toggle"><br /></div>
				<h3 class="hndle"><span><?php _e($title, $this->textdomain); ?></span></h3>
				<div class="inside">
					<?php _e($content, $this->textdomain); ?>
				</div>
			</div>
		<?php
		} // End of display_box

		/**
		 * add_page
		 *
		 * Add a menu and a page
		 *
		 * @package EG-Plugins
		 *
		 * @param
		 * @param
		 * @return none
		 */
		function add_page($args) {

			$default_args = array(
				'id' 				=> '',
				'parent_id'			=> '',
				'type'				=> 'options',
				'page_title'		=> $this->name.__(' settings', $this->textdomain),
				'menu_title'		=> $this->name,
				'access_level'		=> 'manage_options',
				'display_callback'	=> '',
				'option_link' 		=> FALSE,
				'load_callback' 	=> FALSE,
				'load_scripts'		=> FALSE,
				'shortname'			=> null,
				'icon_url'			=> null
			);

			$values = wp_parse_args($args, $default_args);
			if ($values['id']!='' && $values['display_callback']!='') {
				$this->pages[$values['id']] = $values;
				if ($values['option_link']) $this->options_page_id = $values['id'];
				return ($values['id']);
			}
			else
				return (FALSE);
		} // End of add_page

		/**
		  * add_plugin_pages
		  *
		  * Add an option page menu
		  *
		  * @package EG-Plugins
		  * @param 		none
		  * @return 		none
		 */
		function admin_menu() {

			$page_list = array ( 'dashboard' => 'index.php',
								'posts'		=> 'edit.php',
								'options'	=> 'options-general.php',
								'settings'	=> 'options-general.php',
								'tools'		=> 'tools.php',
								'theme'		=> 'themes.php',
								'users'		=> 'users.php',
								'media'		=> 'upload.php',
								'links'		=> 'link-manager.php',
								'pages'		=> 'edit.php?post_type=page',
								'comments'	=> 'edit-comments.php');

			// Add a new submenu under Options:
			if (sizeof($this->pages)>0) {
				foreach ($this->pages as $id => $page) {

					// Create the menu
					if ($page['type'] == 'menu') {

						$hook = add_menu_page(
									__($page['page_title'], $this->textdomain),
									__($page['menu_title'], $this->textdomain),
									$page['access_level'],
									$id,
									array(& $this, $page['display_callback']),
									$page['icon_url'],
									$page['position']
								);
					} // End of menu
					else {
						if ($page['type'] != 'submenu')
							$page['parent_id'] =  $page_list[$page['type']];

						$hook = add_submenu_page(
									$page['parent_id'],
									__($page['page_title'], $this->textdomain),
									__($page['menu_title'], $this->textdomain),
									$page['access_level'],
									$id,
									array(& $this, $page['display_callback'])
								);

						if (isset($this->pages[$page['parent_id']]) && $this->pages[$page['parent_id']]['shortname'] != '') {
							global $submenu;
							$submenu[$page['parent_id']][0][0] = $this->pages[$page['parent_id']]['shortname'];
							$this->pages[$page['parent_id']]['shortname'] = '';
						}
					} // End of submenu

					// Get the hook of the page
					$this->hooks[$page['display_callback']][$id] = $hook;

					// Add load, and print_scripts functions (attached to the hook)
					if ($page['load_callback'] !== FALSE) {
						add_action('load-'.$hook, array(&$this, $page['load_callback']));
					}
					if ($page['load_scripts'] !== FALSE) {
						add_action('admin_print_scripts-'.$hook, array(&$this, $page['load_scripts']));
					}
					// Add the link into the plugin page
					if ($this->options_page_id == $id) {
						add_filter( 'plugin_action_links_' . plugin_basename($this->plugin_corefile),
									array( &$this, 'filter_plugin_actions') );
					}

				} // End of foreach
				unset($this->pages);
			} // End of isset this->pages
		} // End of admin_menu

		/**
		 * get_page_hook
		 *
		 * Return the hook of the page
		 *
		 * @package EG-Plugins
		 *
		 * @param	string	$button_name	Name of the button
		 * @return none
		 */
		function get_page_hook($page_id='', $function = '') {
			if ($page_id == '' || $function == '') return FALSE;
			else return (isset($this->hooks[$function][$page_id]) ? $this->hooks[$function][$page_id] : FALSE);
		} // End of get_page_hook

		/**
		 * add_tinyMCE_button
		 *
		 * Add a TinyMCE button
		 *
		 * @package EG-Plugins
		 *
		 * @param	string	$button_name	Name of the button
		 * @return none
		 */
		function add_tinyMCE_button($button_name, $tinymce_plugin_path, $js_file_name='editor_plugin.js') {
			$index = sizeof($this->tinyMCE_buttons);
			$this->tinyMCE_buttons[$index]->name 	= $button_name;
			$this->tinyMCE_buttons[$index]->js_file = $js_file_name;
			$this->tinyMCE_buttons[$index]->path 	= $tinymce_plugin_path;
		} // End of add_tinyMCE_button

		/**
		 * register_button()
		 * Insert button in wordpress post editor
		 *
		 * @package EG-Plugins
		 * @param array	$buttons	list of buttons
		 * @return array 	$buttons
		 */
		function register_button($buttons) {

			foreach ($this->tinyMCE_buttons as $value) {
				array_push($buttons, $value->name );
			}
			return $buttons;
		} // End of register_button

		/**
		 * add_tinymce_plugin()
		 * Load the TinyMCE plugin : editor_plugin.js
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return $plugin_array
		 */
		function add_tinymce_plugin($plugin_array) {

			foreach ($this->tinyMCE_buttons as $value) {
				$plugin_array[$value->name] = $this->url.$value->path.'/'.$value->js_file;
			}
			return $plugin_array;
		} // End of add_tinymce_plugin

		function tiny_mce_version($version) {
			return ++$version;
		} // End of tiny_mce_version

	} /* End of class */
} /* End of class_exists */

?>