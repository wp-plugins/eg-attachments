<?php
/*
Plugin Name: EG-Plugin
Plugin URI:
Description: Framework for plugin development
Version: 1.00
Author: Emmanuel GEORJON
Author URI: http://www.emmanuelgeorjon.com/
*/

/*
    Copyright 2009 Emmanuel GEORJON  (email : blog@georjon.eu)

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

if (!class_exists('EG_Plugin_100')) {

	/**
	  * Class EG_Plugin
	  *
	  * Provide some functions to create a WordPress plugin
	  *
	 */
	Class EG_Plugin_100 {

		var $plugin_name;
		var $plugin_version;
		var $plugin_path;
		var $plugin_url;
		var $plugin_core_file;

		var $plugin_author_name;
		var $plugin_author_url;
		var $plugin_author_email;

		var $stylesheet;
		var $admin_stylesheet;
		
		var $options_entry;
		var $options;
		var $default_options;

		var $tinyMCE_button;

		var $wp_version_min;
		var $wp_version_max;
		var $wpmu_version_min;
		var $wpmu_version_max;

		var $textdomain;
		var $pages;
		
		/**
		  * Class contructor for PHP 4 compatibility
		  *
		  * @package EG-Plugins
		  * @return object
		  *
		  */
		function EG_Plugin_100($name,
							 $version,
							 $core_file,
							 $options_entry,
							 $default_options,
							 $textdomain,
							 $stylesheet,
							 $admin_stylesheet,
							 $author_name      = '',
							 $author_url       = '',
							 $author_email	   = '',
							 $tinyMCE_button   = FALSE,
							 $cacheexpiration  = 0,
							 $wp_version_min,
							 $wp_version_max   = FALSE,
							 $wpmu_version_min = FALSE,
							 $wpmu_version_max = FALSE) {

			register_shutdown_function(array(&$this, "__destruct"));
			$this->__construct($name,
							 $version,
							 $core_file,
							 $options_entry,
							 $default_options,
							 $textdomain,
							 $stylesheet,
							 $admin_stylesheet,
							 $author_name,
							 $author_url,
							 $author_email,
							 $tinyMCE_button,
							 $cacheexpiration,
							 $wp_version_min,
							 $wp_version_max,
							 $wpmu_version_min,
							 $wpmu_version_max);
		}

		/**
		  * Class contructor
		  * Define the plugin url and path. Declare action INIT and HEAD.
		  *
		  * @package EG-Plugins
		  * @return object
		  */
		function __construct($name,
							 $version,
							 $core_file,
							 $options_entry,
							 $default_options,
							 $textdomain,
							 $stylesheet,
							 $admin_stylesheet,
							 $author_name      = '',
							 $author_url       = '',
							 $author_email	   = '',
							 $tinyMCE_button   = FALSE,
							 $cacheexpiration  = 0,
							 $wp_version_min,
							 $wp_version_max   = FALSE,
							 $wpmu_version_min = FALSE,
							 $wpmu_version_max = FALSE)	{

			$this->plugin_name        = $name;
			$this->plugin_version     = $version;
			$this->options_entry      = $options_entry;
			$this->default_options    = $default_options;
			$this->textdomain         = $textdomain;
			$this->stylesheet         = $stylesheet;
			$this->admin_stylesheet   = $admin_stylesheet;
			$this->plugin_author_name = $author_name;
			$this->plugin_author_url  = $author_url;
			$this->plugin_author_email= $author_email;
			$this->tinyMCE_button     = $tinyMCE_button;
			$this->cacheexpiration	  = $cacheexpiration;
			$this->wp_version_min	  = $wp_version_min;
			$this->wp_version_max	  = $wp_version_max;
			$this->wpmu_version_min	  = $wpmu_version_min;
			$this->wpmu_version_max	  = $wpmu_version_max;

			// Define WP_CONTENT_URL and WP_CONTENT_DIR for WordPress < 2.6
			if ( !defined('WP_CONTENT_URL') )
			    define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
			if ( !defined('WP_CONTENT_DIR') )
			    define( 'WP_CONTENT_DIR', str_replace('\\', '/', ABSPATH) . 'wp-content' );

			if ( !defined( 'WP_PLUGIN_URL' ) )
				define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
			if ( !defined('WP_PLUGIN_DIR') )
			    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

			/* Define the plugin path */
			$plugin_base_path = str_replace('\\','/',plugin_basename(dirname($core_file)));

			$this->plugin_path 		= trailingslashit(WP_PLUGIN_DIR.'/'.$plugin_base_path);
			$this->plugin_url  		= trailingslashit(WP_PLUGIN_URL.'/'.$plugin_base_path);
			$this->plugin_core_file = $this->plugin_path.'/'.basename($core_file);

			/* --- Get Plugin options --- */
			$this->options = $this->get_option();

			add_action('init',           array( &$this, 'init'));
			add_action('plugins_loaded', array(&$this, 'plugins_loaded'), 0);
			
			if (is_admin()) {
				/* Register install and uninstall methods */
				register_activation_hook($this->plugin_core_file, array(&$this, 'install') );

				if ( function_exists('register_uninstall_hook') ) {
					register_uninstall_hook ($this->plugin_core_file, array(&$this, 'uninstall') );
				}
				add_action('admin_header', array( &$this, 'admin_head'));
				add_action('admin_footer', array( &$this, 'admin_footer'));
			}
			else {
				add_action('wp_head',   array( &$this, 'head')  );
				add_action('wp_footer', array( &$this, 'footer'));
			}
		}

		/**
		 * Class destructor
		 *
		 * @package EG-Plugins
		 * @return boolean true
		 */
		function __destruct() {
			// Nothing for the moment
		}

		/**
		 * Object initialization
		 *
		 * Call internationalization features. Add TinyMCE button if required
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return none
		 */
		function init () {

			/* --- Check if WP version is correct --- */
			$this->check_requirements(TRUE);

			/* --- Load translations file --- */
			if (function_exists('load_plugin_textdomain')) {
				if ($this->textdomain != '') {
					if ( !defined('WP_PLUGIN_DIR') ) {
						// for WP < 2.6
						load_plugin_textdomain( $this->textdomain, 'wp-content/plugins/'. dirname(plugin_basename($this->plugin_core_file)).'/lang');
					} else {
						// for WP >= 2.6
						load_plugin_textdomain( $this->textdomain, false, dirname(plugin_basename($this->plugin_core_file)) . '/lang');
					}
				}
			}
			if (is_admin()) {
				// Add only in Rich Editor mode
				if ( $this->tinyMCE_button && get_user_option('rich_editing') == 'true') {

					// add the button for wp2.5 in a new way
					add_filter('mce_external_plugins', array (&$this, 'add_tinymce_plugin' ), 5);
					add_filter('mce_buttons', array (&$this, 'register_button' ), 5);
				}

				if (sizeof($this->pages) > 0)
					add_action( 'admin_menu', array(&$this, 'add_plugin_pages') );
			}
		}

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
			$this->create_widgets();
		}
		
		/**
		 * create_widgets
		 *
		 * Create widgets if required 
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return none
		 */
		function create_widgets () {
			/* nothing at this level */
		}

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
			if (isset($this->options['load_css'])) $load_css = $this->options['load_css'];
			else $load_css = TRUE;
	
			$string = '';
			if ($this->stylesheet != '' && $load_css === TRUE) {
				$string = "\n".'<!-- Generated by '.$this->plugin_name.' '.$this->plugin_version.' by '.$this->plugin_author_name.' ('.$this->plugin_author_url.') -->';
				if(@file_exists(TEMPLATEPATH.'/'.$this->stylesheet)) {
					// Use stylesheet file stored in the current theme directory
					$string .= "\n".'<link rel="stylesheet" href="'.get_stylesheet_directory_uri().'/'.$this->stylesheet.'" type="text/css" media="screen" />';
				} else {
					// Use stylesheet stored in the plugin path
					$string .= "\n".'<link rel="stylesheet" href="'.$this->plugin_url.$this->stylesheet.'" type="text/css" media="screen" />';
				}
			}
			echo $string;
		}

		/**
		 * admin_head
		 *
		 * Implement Head action - Add links to the style sheet
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return none
		 */
		function admin_head() {

			$string = '';
			if ($this->admin_stylesheet != '') {
				$string = "\n".'<!-- Generated by '.$this->plugin_name.' '.$this->plugin_version.' by '.$this->plugin_author_name.' ('.$this->plugin_author_url.') -->';
				$string .= "\n".'<link rel="stylesheet" href="'.$this->plugin_url.$this->admin_stylesheet.'" type="text/css" media="screen" />';
			}
			echo $string;
		}

		/**
		 * Implement Footer action
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return none
		 */
		function footer() {
			// Nothing here
		}
		
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

			// Nothing here
		}
		
		/**
		 * register_button()
		 * Insert button in wordpress post editor
		 *
		 * @package EG-Plugins
		 * @param array	$buttons	list of buttons
		 * @return array 	$buttons
		 */
		function register_button($buttons) {

			array_push($buttons, $this->tinyMCE_button );
			return $buttons;
		}

		/**
		 * add_tinymce_plugin()
		 * Load the TinyMCE plugin : editor_plugin.js
		 *
		 * @package EG-Plugins
		 * @param none
		 * @return $plugin_array
		 */
		function add_tinymce_plugin($plugin_array) {

			$plugin_array[$this->tinyMCE_button] = $this->plugin_url.'tinymce/editor_plugin.js';
			
			return $plugin_array;
		}

		function add_page($page_type, $page_title, $menu_title, $access_level, $page_url, $callback) {
			$index = sizeof($this->pages);
			$this->pages[$index]->type         = $page_type;
			$this->pages[$index]->page_title   = $page_title;
			$this->pages[$index]->menu_title   = $menu_title;
			$this->pages[$index]->access_level = $access_level;
			$this->pages[$index]->page_url     = $page_url;
			$this->pages[$index]->callback     = $callback;
		}
		
		
		/**
		 * get_option
		 *
		 * Get the plugin options. If options don't exists, create them. In case of plugin upgrate, update them.
		 *
		 * @package EG-Plugins
		 * @param 		none
		 * @return array	$options	list of the options and values
		 */
		function get_option() {
			// if option_entry === FALSE, plugin hasn't any options
			if (! $this->options_entry) {
				$options = FALSE;
			} else {
				$options = get_option( $this->options_entry );
				// if $opions === FALSE, options are not initiated yet
				if ( $options === FALSE) {
					// Create option from the defaults
					$this->default_options['version'] = $this->plugin_version;
					// $this->options = $this->default_options;
					add_option($this->options_entry, $this->default_options);
				}
				else {
					// Plugin previously installed. Check the version and update options
					if (version_compare($options['version'], $this->plugin_version, '<')) {
						$new_options = array();
						foreach ($this->default_options as $key => $value) {
							if (isset($options[$key])) $new_options[$key] = $options[$key];
							else $new_options[$key] = $value;
						}
						$new_options['version'] = $this->plugin_version;
						update_option($this->options_entry, $new_options);
						// $this->options = $new_options;
						$optios = $new_options;
					}
				}
			}
			return $options;
		}

		/**
		  * Check_requirements
		  *
		  * Check if the wordpress version meets the plugin requirements.
		  *
		  * @package EG-Plugins
		  * @param 		none
		  * @return 		none
		  */
		function check_requirements($display_msg) {
			global $wp_version, $wpmu_version;

			$value = TRUE;
			if ($display_msg && is_admin() && strpos($_SERVER['REQUEST_URI'], 'plugins.php')) {
				// Are we using WP MU ?
				$is_wpmu = (isset($wpmu_version) || (strpos($wp_version, 'wordpress-mu') !== false));

				$value = FALSE;
				if ($is_wpmu) {
					if ($this->wpmu_version_min) {
						$value = version_compare($wpmu_version, $this->wpmu_version_min, '>=');
						if ($this->wpmu_version_max) $value = $value && version_compare($wpmu_version, $this->wpmu_version_max, '<=');
					}
				} else {
					$value = version_compare($wp_version, $this->wp_version_min, '>=');
					if ($this->wp_version_max) $value = $value && version_compare($wp_version, $this->wp_version_max, '<=');
				}

				// if $value isn't empty, we have a message to display
				if (!$value) {
					// Build the message
					$string = '<strong>'.$this->plugin_name.'</strong>'.__(' requires ', $this->textdomain). 'WordPress '.$this->wp_version_min;
					if ($this->wp_version_max)   $string .= __(' to ',  $this->textdomain).$this->wp_version_max;
					if ($this->wpmu_version_min) $string .= __(', or ', $this->textdomain).'WordPress MU '.$this->wpmu_version_min;
					if ($this->wpmu_version_max) $string .= __(' to ',  $this->textdomain).$this->wpmu_version_max;

					// Display the message
					add_action('admin_notices',
							create_function('', 'echo \'<div id="message" class="error fade"><p>'.$string.'</p></div>\';'));
				}
			}
			return ($value);
		}


		/**
		  * Install
 		  *
		  * Actions required to install the plugin
		  *
		  * @package EG-Plugins
		  * @param 		none
		  * @return 		none
		  */
		function install() {
			// Run get_option to create the options
			$this->get_option();
		}

		/**
		  * Uninstall
		  *
		  * Actions required to uninstall the plugin.
		  *
		  * @package EG-Plugins
		  * @param 		none
		  * @return 		none
		 */
		function uninstall() {
			if ($this->options_entry) delete_option($this->options_entry);
		}

		/**
		  * add_plugin_pages
		  *
		  * Add an option page menu
		  *
		  * @package EG-Plugins
		  * @param 		none
		  * @return 		none
		 */
		function add_plugin_pages() {
			// Add a new submenu under Options:
			foreach ($this->pages as $page) {
				switch ($page->type) {
					case 'posts': 
						add_posts_page($page->page_title,
								     $page->menu_title,
									 $page->access_level,
									 $page->page_url,
									 array(&$this, $page->callback));
						break;
					case 'options': 
						add_options_page($page->page_title,
								     $page->menu_title,
									 $page->access_level,
									 $page->page_url,
									 array(&$this, $page->callback));
						break;
					case 'tools':
						add_management_page($page->page_title,
								     $page->menu_title,
									 $page->access_level,
									 $page->page_url,
									 array(&$this, $page->callback));
						break;
				}
			}
		}

		/**
		 * Adds an action link to the plugins page
		 */
		/*
		function settings_plugin_page($links, $file) {
			static $this_plugin;

			if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

			if( $file == $this_plugin ){
				$settings_link = '<a href="index.php?page=sayfa_sayac/sayfa_sayac_de.php">' . __('Settings') . '</a>';
				$links = array_merge( array($settings_link), $links);
			}
			return $links;
		}
		*/

		/**
		  * display_message
		  *
		  * Generates a complete html form for widget control panel
		  *
		  * @package EG-Plugins
		  * @param 	string	$message	message to display
		  * @param 	string	$status	update, error, warning, information
		  * @return 		none
		  */
		function display_message($message, $status ='') {
			if ( $message != '') {
			?>
				<div id="message" class="<?php echo ($status != '') ? $status :'updated'; ?> fade">
					<p><strong><?php echo $message; ?></strong></p>
				</div>
			<?php
			}
		} /* --- end of display_message --- */
	} /* End of class */
} /* End of class_exists */

?>