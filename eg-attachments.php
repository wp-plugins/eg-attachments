<?php
/*
Plugin Name: EG-Attachments
Plugin URI:  http://www.emmanuelgeorjon.com/en/eg-attachments-plugin-1233
Description: Shortcode displaying lists of attachments for a post
Version: 1.2.0
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

define('EG_ATTACH_COREFILE', 	  __FILE__);
define('EG_ATTACH_VERSION', 	  '1.2.0');
define('EG_ATTACH_OPTIONS_ENTRY', 'EG-Attachments-Options');

$EG_ATTACH_DEFAULT_OPTIONS = array(
	'shortcode_auto'			=> 0,
	'shortcode_auto_where'		=> 'post',
	'shortcode_auto_title'  	=> '',
	'shortcode_auto_title_tag'	=> 'h2',
	'shortcode_auto_size'		=> 'large',
	'shortcode_auto_doc_type'	=> 'document',
	'shortcode_auto_label'		=> 'filename',
	'force_saveas'		 		=> 0
);

require_once('lib/eg-plugin.inc.php');

if (is_admin()) {
	require_once('eg-attachments-admin.inc.php');
}
else {
	require_once('eg-attachments-public.inc.php');
}

?>