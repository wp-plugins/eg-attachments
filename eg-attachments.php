<?php
/*
Plugin Name: EG-Attachments
Plugin URI: http://www.emmanuelgeorjon.com/eg-attachments-1233
Description: Shortcode displaying lists of attachments for a post
Version: 2.0.3
Author: Emmanuel GEORJON
Author http://www.emmanuelgeorjon.com/
License: GPL2
Text Domain: eg-attachments
Domain Path: /lang/
*/

/*  Copyright 2008-2014  Emmanuel GEORJON  (email : blog@emmanuelgeorjon.com)

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

define('EGA_VERSION', 		'2.0.3'		);
define('EGA_COREFILE',		__FILE__	);
define('EGA_ENABLE_CACHE',	TRUE		);

/* --- 
   Loading libraries 
   -------------------------------------------- */
if (! class_exists('EG_Plugin_134')) {
	require('lib/eg-plugin.inc.php');
}

if (! class_exists('EG_Widget_220')) {
	require_once('lib/eg-widgets.inc.php');
}

/* --- 
   Loading plugin functions 
   -------------------------------------------- */
if (! class_exists('EG_Attachments_Common')) {
	require('inc/eg-attachments-common.inc.php');
}

if (is_admin()) {
	require_once('inc/eg-attachments-admin.inc.php');
}
else {
	require_once('inc/eg-attachments-public.inc.php');
}

/* --- 
   Loading Widgets 
   -------------------------------------------- */
require_once('inc/eg-attachments-widgets.inc.php');


?>