<?php
/*
Plugin Name: EG-Attachments
Plugin URI:  http://www.emmanuelgeorjon.com/en/eg-attachments-plugin-1233
Description: Shortcode displaying lists of attachments for a post
Version: 1.8.6
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

define('EG_ATTACH_COREFILE', 		__FILE__);
define('EG_ATTACH_VERSION',  		'1.8.6' );
define('EG_ATTACH_OPTIONS_ENTRY', 	'EG-Attachments-Options');

define('EG_ATTACH_DEBUG_MODE',	FALSE);

require_once('eg-attachments-config.inc.php');

if (! class_exists('EG_Plugin_118')) {
	require('lib/eg-plugin.inc.php');
}

if (is_admin()) {
	require_once('eg-attachments-admin.inc.php');
}
else {
	require_once('eg-attachments-public.inc.php');
}

require_once('lib/eg-widgets280.inc.php');
require_once('eg-attachments-widgets.inc.php');

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
	$options = get_option(EG_ATTACH_OPTIONS_ENTRY);
	if ( isset($options) && $options['uninstall_del_options']) {
		delete_option(EG_ATTACH_OPTIONS_ENTRY);
	}
} // End of eg_attachments_uninstall

register_uninstall_hook (EG_ATTACH_COREFILE, 'eg_attachments_uninstall' );
?>