<?php
/*
Plugin Name: EG-Attachments-v2
Plugin URI: http://www.emmanuelgeorjon.com/eg-attachments-1233
Description: Shortcode displaying lists of attachments for a post
Version: 2.0.0-beta
Author: Emmanuel GEORJON
Author http://www.emmanuelgeorjon.com/
License: GPL2
*/

/*  Copyright 2008-2012  Emmanuel GEORJON  (email : blog@emmanuelgeorjon.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('EGA_VERSION', 	'2.0.0-beta2');
define('EGA_COREFILE',	__FILE__);
//define('EG_PLUGIN_ENABLE_CACHE', FALSE);

if (! class_exists('EG_Plugin_130')) {
	require('lib/eg-plugin.inc.php');
}

if (! class_exists('EG_Attachments_Common')) {
	require('inc/eg-attachments-common.inc.php');
}

if (is_admin()) {
	require_once('inc/eg-attachments-admin.inc.php');
}
else {
	require_once('inc/eg-attachments-public.inc.php');
}

require_once('lib/eg-widgets280.inc.php');
require_once('inc/eg-attachments-widgets.inc.php');

?>