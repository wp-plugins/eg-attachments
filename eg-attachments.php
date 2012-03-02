<?php
/*
Plugin Name: EG-Attachments
Plugin URI: http://www.emmanuelgeorjon.com/eg-attachments-1233
Description: Shortcode displaying lists of attachments for a post
Author: Emmanuel GEORJON
Version: 1.9.4.3
Author URI: http://www.emmanuelgeorjon.com/
Text Domain: eg-attachments
Domain Path: /lang
*/

/*
    Copyright 2009-2011 Emmanuel GEORJON (email : blog@emmanuelgeorjon.com)

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

define('EGA_COREFILE', 		__FILE__);
define('EGA_VERSION',  		'1.9.4.3' );
define('EGA_OPTIONS_ENTRY',	'EG-Attachments-Options');
define('EGA_TEXTDOMAIN',    'eg-attachments');
define('EGA_SHORTCODE',     'attachments');

define('EGA_DEBUG_MODE', FALSE);

require_once('inc/eg-attachments-config.inc.php');

if (! class_exists('EG_Plugin_127')) {
	require('lib/eg-plugin.inc.php');
}

/**
 * eg_series_custom_admin_bar
 *
 * Customize the admin bar
 *
 * @param 	none
 * @return 	none
 */
function eg_attachments_custom_admin_bar($wp_admin_bar) {

	if (!is_admin_bar_showing())
		return;

	$edit_posts_cap = current_user_can('edit_posts');
	if (!$edit_posts_cap)
		return;

	$admin_cap = current_user_can('manage_options');

	global $wp_version;
	if (version_compare($wp_version, '3.2.99', '>')) {

		$wp_admin_bar->add_node( array(
			'id' 		 => 'egplugins',
			'title' 	 => __('EG-Plugins'))
		);

		$wp_admin_bar->add_node( array(
			'parent' 	 => 'egplugins',
			'id' 	 	 => 'ega_adm_bar',
			'title'  	 => __('EG-Attachments'))
		);

		$options = get_option(EGA_OPTIONS_ENTRY);
		if ($edit_posts_cap && $options['stats_enable']) {
			$wp_admin_bar->add_node( array(
				'parent' => 'ega_adm_bar',
				'id' 	 => 'ega_stats',
				'title'  => __('Statistics', EGA_TEXTDOMAIN),
				'href' 	 => admin_url('tools.php?page=ega_stats'))
			);
		}

		if ($admin_cap) {
			$wp_admin_bar->add_node( array(
				'parent' => 'ega_adm_bar',
				'id' 	 => 'ega_settings',
				'title'  => __('Settings'),
				'href' 	 => admin_url('options-general.php?page=ega_options'))
			);
		}
	}
	else {
		global $wp_admin_bar;

		$wp_admin_bar->add_menu( array(
			'id' 	=> 'egplugins',
			'title' => __('EG-Plugins'))
		);

		$wp_admin_bar->add_menu( array(
			'parent' 	 => 'egplugins',
			'id' 	 	 => 'ega_adm_bar',
			'title'  	 => __('EG-Attachments'))
		);

		$options = get_option(EGA_OPTIONS_ENTRY);
		if ($edit_posts_cap && $options['stats_enable']) {
			$wp_admin_bar->add_menu( array(
				'parent' => 'ega_adm_bar',
				'id' 	 => 'ega_stats',
				'title'  => __('Statistics', EGA_TEXTDOMAIN),
				'href' 	 => admin_url('tools.php?page=ega_stats'))
			);
		}

		if ($admin_cap) {
			$wp_admin_bar->add_menu( array(
				'parent' => 'ega_adm_bar',
				'id' 	 => 'ega_settings',
				'title'  => __('Settings'),
				'href' 	 => admin_url('options-general.php?page=ega_options'))
			);
		}
	} // Version < 3.3
} // End of eg_attachments_custom_admin_bar

if (is_admin()) {
	require_once('inc/eg-attachments-admin.inc.php');
}
else {
	require_once('inc/eg-attachments-public.inc.php');
}

require_once('lib/eg-widgets280.inc.php');
require_once('inc/eg-attachments-widgets.inc.php');

/**
 * eg_attachments_uninstall
 *
 * Delete option of the plugin during uninstallation
 *
 * @package EG-Attachments
 *
 * @param 	none
 * @return	none
 */
function eg_attachments_uninstall() {
	$options = get_option(EGA_OPTIONS_ENTRY);
	if ( isset($options) && $options['uninstall_del_options']) {
		delete_option(EGA_OPTIONS_ENTRY);
	}
} // End of eg_attachments_uninstall

register_uninstall_hook (EGA_COREFILE, 'eg_attachments_uninstall' );

?>