<?php
/*
Plugin Name: EG-Attachments
Plugin URI:  http://www.emmanuelgeorjon.com/en/eg-attachments-plugin-1233
Description: Shortcode displaying lists of attachments for a post
Version: 1.0.1
Author: Emmanuel GEORJON
Author URI: http://www.emmanuelgeorjon.com/
*/

/*  Copyright 2009 Emmanuel GEORJON  (email : blog@georjon.eu)

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

require_once('lib/eg-plugin.inc.php');

if (! class_exists('EG_Attachments')) {

	/**
	 * Class EG_Attachments
	 *
	 * Implement a shortcode to display the list of attachments in a post.
	 *
	 * @package EG-Attachments
	 */
	Class EG_Attachments extends EG_Plugin_100 {

		var $icon_height = array( 'large' => 48, 'medium' => 32, 'small' => 16);
		var $icon_width  = array( 'large' => 48, 'medium' => 32, 'small' => 16);

		var $eg_attachment_shortcode_defaults = array(
			'orderby'  => 'post_title ASC',
			'size'     => 'large',
			'doctype'  => 'document',
			'docid'    => 0,
			'title'    => '',
			'titletag' => 'h2'
		);
		
		
		/**
		 * Implement INIT action
		 *
		 * Add filter, hooks or action.
		 *
		 * @package EG-Attachments
		 * @param none
		 * @return none
		 */
		function init() {

			parent::init();

			// Add only in Rich Editor mode
			if ( current_user_can('edit_posts') && current_user_can('edit_pages') && get_user_option('rich_editing') == 'true') {
				// add the button for wp2.5 in a new way
				add_filter("mce_external_plugins", array (&$this, 'add_tinymce_plugin' ), 5);
				add_filter('mce_buttons', array (&$this, 'register_button' ), 5);
			}
			if (! is_admin()) {
				add_shortcode('attachments', array(&$this, 'get_attachments'));
			}
		} /* End of init */

		/**
		 * Implement Head action
		 *
		 * Add links to the style sheet
		 *
		 * @package EG-Attachments
		 * @param none
		 * @return htm code
		 */
		function head() {
			parent::head();
			echo "\n".'<!-- End of eg-attachments -->'."\n";
		} /* End of Head */

		/**
		  *  icon_dirs() - Add the icon path of the plugin, to the list of paths of WordPress icons
		  *
		  * @package EG-Attachments
		  *
		  * @package EG-Attachments
		  * @param $args	array		list of path and url (array( path1 => url1, path2 => url2 ...))
		  * @return 		array		the previous array, with additional paths
		  */
		function icon_dirs($args) {
			// If $args is not an array => return directly the value
			if (!is_array($args)) $new_args = $args ;
			else {
				// Add the icons path of the current plugin
				$new_args = array_merge(array($this->plugin_path.'images' => $this->plugin_url.'images'),$args);
			}
			return ($new_args);
		} /* End of icon_dirs */

		/**
		  *  get_file_size() - Try to get the size of the specified file
		  *
		  * @package EG-Attachments
		  *
		  * @package EG-Attachments
		  * @param 	string	$file_url	url of attachment
		  * @return	float 		size of the attachment
		  */
		function get_file_size($file_url) {

			/* Objective: get the size of the file specified
			     the php function filesize requires the path, not the url
		                try to find the path of the path.
		          */
			$site_url  = get_bloginfo('siteurl');

			// Parse the site url, to detect if we have only hostname, or hostname/path
			$url_array = parse_url($site_url);

			// Replace backslashes with slashes in case of windows hosting
			$abspath   = str_replace('\\','/', ABSPATH);

			// Remove the path part from the ABSPATH
			//if (isset($url_array['path']) && $url_array['path'] != '') $abspath = str_replace(trailingslashit($url_array['path']), '', $abspath);

			// Get the path of the file
			$file_path = $abspath.str_replace($site_url, '', $file_url);

			// size calculation
			$docsize = @filesize($file_path);
			if ($docsize === FALSE) $docsize = '';
			else {
				if ($docsize > 1000000) $docsize = intval($docsize/100000)/10 . __(' MB', $this->text_domain);
				else if ($docsize > 1000) $docsize = intval($docsize/100)/10 . __(' KB', $this->text_domain);
				else $docsize = $docsize . __(' bytes', $this->text_domain);
			}
			return ($docsize);
		} /* End of get_file_size */

		/**
		  *  get_icon() - Get the thumbnail of the atttachment
		  *
		  * @package EG-Attachments
		  * @param int 	$id			attachment id
		  * @param object $attachment 	the attachment metadata
		  * @param string $size 			size of the thumbnail (small, medium or large)
		  * @return string html entities IMG
		  */
		function get_icon($id, $attachment, $size) {
			$output      = '';

			$width  = $this->icon_width[$size];
			$height = $this->icon_height[$size];
			if (! $icon_url = wp_mime_type_icon($id) ) {
				$icon_url = trailingslashit(get_bloginfo('wpurl')).WPINC.'/images/crystal/default.png';
			}
			$output .= '<img src="'.$icon_url.'" width="'.$width.'" height="'.$height.'" alt="'.$description.'" />';

			return ($output);
		} /* end of get_icon */

		/**
		  *  get_attachments() - Display the list of attachments
		  *
		  * {@internal Missing Long Description}
		  *
		  * @package EG-Attachments
		  * @param array $attr shortcode attributs list
		  * @return string List of attachments (dl / dt /dd liste)
		  */
		function get_attachments($attr)  {
			global $post;
		
			add_filter('icon_dirs', array(&$this, 'icon_dirs'));
			
			// Preparing parameters and query
			$this->eg_attachment_shortcode_defaults['id'] = $post->ID;
			extract( shortcode_atts( $this->eg_attachment_shortcode_defaults, $attr ));
			$id      = intval($id);
			$orderby = addslashes($orderby);

			if ($docid != 0) $doc_list = split(',', $docid);
			else $doc_list = array();

			// get attachments
			$attachments = wp_cache_get( 'attachments', 'eg-attachments' );
			if (! $attachments) {
				$attachments = get_children('post_parent='.$id.'&post_type=attachment&orderby="'.$orderby.'"');
				wp_cache_set('attachments', $attachments, 'eg-attachments', $this->cacheexpiration);
			}
			// if no attachments, stop and exit
			if (! $attachments) {
				return '';
			}

			// Display title
			$output = '';
			if ($title != '') $output .= '<'.$titletag.'>'.htmlspecialchars(stripslashes(strip_tags($title))).'</'.$titletag.'>';

			// Display attachment list
			foreach ( $attachments as $attach_id => $attachment ) {
				if (sizeof($doc_list) == 0 || array_search($attach_id, $doc_list) !== FALSE) {
					$mime_type = substr($attachment->post_mime_type,0,5);
					if ( ($doctype == 'image' && $mime_type == 'image') ||
					     ($doctype == 'document' && $mime_type != 'image') ) {
						$file_size = $this->get_file_size($attachment->guid);
						switch ($size) {
							case 'large':
								if ($file_size != '') $string_file_size = '<strong>'.__('Size: ', $this->text_domain).'</strong>'.$file_size;
								$output .= '<dl class="attachments attachments-large"><dt class="icon">'.
										   '<a href="'.$attachment->guid.'" title="'.$attachment->post_title.'">'.$this->get_icon($attach_id, $attachment, $size).'</a></dt>'.
									 '<dd class="caption"><strong>'.__('Title: ', $this->text_domain).'</strong>'.'<a href="'.$attachment->guid.'" title="'.$attachment->post_title.'">'.$attachment->post_title.'</a><br />'.
									 '<strong>'.__('Description: ', $this->text_domain).'</strong>'.$attachment->post_excerpt.'<br />'.
									 '<strong>'.__('File: ', $this->text_domain).'</strong>'.basename($attachment->guid).'<br />'.
									$string_file_size.
									'</dd>'.
									 '</dl>';
							break;

							case 'medium':
								if ($file_size != '') $string_file_size = '('.$file_size.')';
								$output .= '<dl class="attachments attachments-medium">'.
										'<dt class="icon">'.'<a href="'.$attachment->guid.'" title="'.$attachment->post_title.'">'.$this->get_icon($attach_id, $attachment, $size).'</a></dt>'.
									 '<dd class="caption"><strong>'.__('File: ', $this->text_domain).'</strong><a href="'.$attachment->guid.'" title="'.$attachment->post_title.'">'.basename($attachment->guid).'</a> '.$string_file_size.'<br />'.
									 '<strong>'.__('Description: ', $this->text_domain).'</strong>'.$attachment->post_excerpt.'</dd>'.
									 '</dl>';
							break;

							case 'small':
								if ($file_size != '') $string_file_size = '('.$file_size.')';
								$output .= '<dl class="attachments attachments-small"><dt class="icon">'.
								           '<a href="'.$attachment->guid.'" title="'.$attachment->post_title.'">'.$this->get_icon($attach_id, $attachment, $size).'</a></dt>'.
										   '<dd class="caption"><a href="'.$attachment->guid.'" title="'.$attachment->post_title.'">'.basename($attachment->guid).'</a> '.$string_file_size.'</dd></dl>';
							break;
						}
					}
				}
			}
			remove_filter('icon_dirs', array(&$this, 'icon_dirs'));
			
			return $output;
		} /* --- End of get_attachments -- */

	} /* End of Class */
} /* End of if class_exists */

$eg_attach = new EG_Attachments('EG-Attachments',				/* plugin name 		*/
							'1.0.0',							/* plugin version 		*/
							__FILE__, 							/* pluginbasename	*/
							'',									/* option entry 		*/
							FALSE,								/* default options 	*/
							'eg-attachments',					/*  text domain 		*/
							'eg-attachments.css',				/* stylesheet file name 	*/
							FALSE,								/* admin stylesheet         */
							'Emmanuel GEORJON',					/* author name 		*/
							'http://www.emmanuelgeorjon.com/',	/* author url 		*/
							'blog@georjon.eu',					/* author email		*/
							'EGAttachments',					/* tinyMCE button 	*/
							3600,								/* Cache expiration         */
							'2.5',								/* First WP release          */
							FALSE,								/* Last WP release	*/
							FALSE,								/* First WP MU release	*/
							FALSE);								/* Last WP MU release	*/
?>