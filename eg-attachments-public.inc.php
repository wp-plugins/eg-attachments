<?php

if (! class_exists('EG_Attachments')) {

	/**
	 * Class EG_Attachments
	 *
	 * Implement a shortcode to display the list of attachments in a post.
	 *
	 * @package EG-Attachments
	 */
	Class EG_Attachments extends EG_Plugin_102 {

		var $icon_height = array( 'large' => 48, 'medium' => 32, 'small' => 16);
		var $icon_width  = array( 'large' => 48, 'medium' => 32, 'small' => 16);
		var $wp_before_260;

		var $eg_attachment_shortcode_defaults = array(
			'orderby'  		=> 'title ASC',
			'size'     		=> 'large',
			'doctype'  		=> 'document',
			'docid'    		=> 0,
			'title'    		=> '',
			'titletag' 		=> 'h2',
			'label'    		=> 'filename',
			'force_saveas'	=> -1,
			'fields'		=> 'caption',
			'icon'			=> 1
		);

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

			// Clear cache when adding or delete attachment
			add_action('add_attachment',    array(&$this, 'clean_cache' ));
			add_action('delete_attachment', array(&$this, 'clean_cache' ));

			$this->wp_before_260 = version_compare($wp_version, '2.6', '<');

			if (! is_admin()) {

				add_shortcode('attachments', array(&$this, 'get_attachments'));
				if ($this->options['shortcode_auto']>0) {
					add_filter('the_content', array(&$this, 'shortcode_auto'));
				}
			}
		} /* End of init */


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
		}

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
			if (!is_array($args))
				$new_args = $args ;
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
		  * @return	float 				size of the attachment
		  */
		function get_file_size($file_url) {

			/* Objective: get the size of the file specified
			     the php function filesize requires the path, not the url
		                try to find the path of the path.
		    */
			$site_url  = get_bloginfo('siteurl');

			// Parse the site url, to detect if we have only hostname, or hostname/path
			// $url_array = parse_url($site_url);

			// Replace backslashes with slashes in case of windows hosting
			$abspath   = str_replace('\\','/', ABSPATH);

			// Get the path of the file
			$file_path = $abspath.str_replace($site_url, '', $file_url);

			// size calculation
			$docsize = @filesize($file_path);
			if ($docsize === FALSE) $docsize = '';
			else {
				$size_value = split(' ',size_format($docsize, 0)); // WP function found in file wp-includes/functions.php
				$docsize = $size_value[0].' '.__($size_value[1], $this->textdomain);
			}

			return ($docsize);
		} /* End of get_file_size */

		/**
		  *  get_icon() - Get the thumbnail of the atttachment
		  *
		  * @package EG-Attachments
		  * @param int 		$id				attachment id
		  * @param object 	$attachment 	the attachment metadata
		  * @param string 	$size 			size of the thumbnail (small, medium or large)
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


		function mime_type_cmp_asc($a, $b)
		{
			return strcmp($a->post_mime_type, $b->post_mime_type);
		}

		function mime_type_cmp_desc($a, $b)
		{
			return strcmp($b->post_mime_type,$a->post_mime_type);
		}

		/**
		  *  get_attachments() - Display the list of attachments
		  *
		  * {@internal Missing Long Description}
		  *
		  * @package EG-Attachments
		  * @param 	array 	$attr 	shortcode attributs list
		  * @return string 			List of attachments (dl / dt /dd liste)
		  */
		function get_attachments($attr)  {
			global $post, $wp_version;

			add_filter('icon_dirs', array(&$this, 'icon_dirs'));

			// Preparing parameters and query
			$this->eg_attachment_shortcode_defaults['id'] = $post->ID;
			extract( shortcode_atts( $this->eg_attachment_shortcode_defaults, $attr ));
			$id      = intval($id);
			$orderby = addslashes($orderby);

			if ($force_saveas < 0) {
				// Take default options
				$force_saveas = $this->options['force_saveas'];
			}

			list($order_by, $order) = split(' ', $orderby);
			if ($this->wp_before_260)
				$order_by = 'post_'.$order_by;

			if ($order == '') $order = 'ASC';

			// get attachments
			$attachments = wp_cache_get( 'attachments', 'eg-attachments' );
			if ($attachments === FALSE || !isset($attachments[$id])) {

				$attachment_list  = get_children( array('post_parent' => $id,
													'numberposts'	=> -1,
													'post_type'		=> 'attachment',
													'orderby'		=> $order_by,
													'order'			=> $order
												)
											);
				if ($attachment_list) {
					$attachments[$id] = $attachment_list;
					wp_cache_set('attachments', $attachments, 'eg-attachments', $this->cacheexpiration);
				}
			}

			// if no attachments, stop and exit
			if ($attachments === FALSE || !isset($attachments[$id])) {
				return '';
			}

			if ($order_by == 'mime') {
				if ($order == 'ASC')
					usort($attachments[$id], array(&$this, 'mime_type_cmp_asc'));
				else
					usort($attachments[$id], array(&$this, 'mime_type_cmp_desc'));
			}

			if ($docid == '0') {
				$doc_list = array();
			}
			else {
				if ($docid == 'first') {
					$temp = current($attachments[$id]);
					$doc_list = array( $temp->ID );
				}
				elseif ($docid == 'last') {
					$temp = end($attachments[$id]);
					$doc_list = array( $temp->ID );
				}
				else {
					$doc_list = split(',', $docid);
				}
			}

			// Display title
			$output = '';

			// Display attachment list
			foreach ( $attachments[$id] as $attachment ) {
				if (sizeof($doc_list) == 0 || array_search($attachment->ID, $doc_list) !== FALSE) {
					$mime_type = substr($attachment->post_mime_type,0,5);
					if ( $doctype == 'all' ||
					    ($doctype == 'image' && $mime_type == 'image') ||
					    ($doctype == 'document' && $mime_type != 'image') ) {
						$file_size = $this->get_file_size($attachment->guid);
						$attachment_title = htmlspecialchars(strip_tags($attachment->post_title));

						if ($force_saveas) {
							$link = '<a target="_blank" title="'.$attachment_title.'" href="'.$this->plugin_url.'eg_attach.php?mime='.$attachment->post_mime_type.'&url='.$attachment->guid.'">';
						}
						else {
							$link = '<a title="'.$attachment_title.'" href="'.$attachment->guid.'">';
						}

						switch ($size) {
							case 'large':
								if ($file_size != '') $string_file_size = '<strong>'.__('Size: ', $this->textdomain).'</strong>'.$file_size;
								if ($icon) {
									$output .= '<dl class="attachments attachments-large"><dt class="icon">'.
											   $link.$this->get_icon($attachment->ID, $attachment, $size).'</a></dt>'.
										 '<dd class="caption"><strong>'.__('Title: ', $this->textdomain).'</strong>'.$link.$attachment_title.'</a><br />'.
										(($attachment->post_excerpt==''||strpos($fields,'caption')===FALSE)?'':'<strong>'.__('Caption: ', $this->textdomain).'</strong>'.$attachment->post_excerpt.'<br />').
										(($attachment->post_content==''||strpos($fields,'description')===FALSE)?'':'<strong>'.__('Description: ', $this->textdomain).'</strong>'.$attachment->post_content.'<br />').
										'<strong>'.__('File: ', $this->textdomain).'</strong>'.basename($attachment->guid).'<br />'.
										$string_file_size.
										'</dd>'.
										 '</dl>';
								}
								else {
									$output .= '<li class="attachments attachments-large">'.
										'<strong>'.__('Title: ', $this->textdomain).'</strong>'.$link.$attachment_title.'</a><br />'.
										(($attachment->post_excerpt==''||strpos($fields,'caption')===FALSE)?'':'<strong>'.__('Caption: ', $this->textdomain).'</strong>'.$attachment->post_excerpt.'<br />').
										(($attachment->post_content==''||strpos($fields,'description')===FALSE)?'':'<strong>'.__('Description: ', $this->textdomain).'</strong>'.$attachment->post_content.'<br />').
										'<strong>'.__('File: ', $this->textdomain).'</strong>'.basename($attachment->guid).'<br />'.
										$string_file_size.
										'</li>';
								}
							break;

							case 'medium':
								if ($file_size != '') $string_file_size = '('.$file_size.')';
								if ($icon) {
									$output .= '<dl class="attachments attachments-medium">'.
											'<dt class="icon">'.$link.$this->get_icon($attachment->ID, $attachment, $size).'</a></dt>'.
										 '<dd class="caption"><strong>';
									if  ($label == 'doctitle') {
										$output .= __('Title: ', $this->textdomain).'</strong>'.$link.$attachment_title.'</a> '.$string_file_size.'<br />';
									}
									else {
										$output .= __('File: ', $this->textdomain).'</strong><a href="'.$attachment->guid.'" title="'.$attachment_title.'">'.basename($attachment->guid).'</a> '.$string_file_size.'<br />';
									}
									$output .= (($attachment->post_excerpt==''||strpos($fields,'caption')===FALSE)?'':'<strong>'.__('Caption: ', $this->textdomain).'</strong>'.$attachment->post_excerpt);
									$output .= '</dd></dl>';
								}
								else {
									$output .= '<li class="attachments attachments-medium">';
									if  ($label == 'doctitle') {
										$output .= __('Title: ', $this->textdomain).'</strong>'.$link.$attachment_title.'</a> '.$string_file_size.'<br />';
									}
									else {
										$output .= __('File: ', $this->textdomain).'</strong><a href="'.$attachment->guid.'" title="'.$attachment_title.'">'.basename($attachment->guid).'</a> '.$string_file_size.'<br />';
									}
									$output .= (($attachment->post_excerpt==''||strpos($fields,'caption')===FALSE)?'':'<strong>'.__('Caption: ', $this->textdomain).'</strong>'.$attachment->post_excerpt);
									$output .= '</li>';
								}
							break;

							case 'small':
								if ($file_size != '') $string_file_size = '('.$file_size.')';
								if ($icon) {
									$output .= '<dl class="attachments attachments-small"><dt class="icon">'.
								           $link.$this->get_icon($attachment->ID, $attachment, $size).'</a></dt>'.
										   '<dd class="caption">'.$link.($label=="doctitle"?$attachment_title:basename($attachment->guid)).'</a> '.$string_file_size.'</dd></dl>';
								}
								else {
									$output .= '<li class="attachments attachments-small">'.
										   $link.($label=="doctitle"?$attachment_title:basename($attachment->guid)).'</a> '.$string_file_size.
										   '</li>';
								}
							break;
						}
					}
				}
			}

			if ($output != '' && ! $icon) {
				$output = '<ul>'.$output.'</ul>';
			}

			if ($output != '' && $title != '') {
				$output = '<'.$titletag.'>'.htmlspecialchars(stripslashes(strip_tags($title))).'</'.$titletag.'>'.$output;
			}

			if ($output != '') {
				$output = '<div class="attachments">'.$output.'</div>';
			}

			remove_filter('icon_dirs', array(&$this, 'icon_dirs'));

			return $output;
		} /* --- End of get_attachments -- */

		/**
		 * shortcode_auto
		 *
		 * Display list of attachment in the post content
		 *
		 * @param 	strong	$content	post_content
		 * @return 	string				modified post content
		 */
		function shortcode_auto($content = '') {

			if ($this->options['shortcode_auto'] > 0) {
				$display = ($this->options['shortcode_auto_where'] != 'post' || is_single() || is_page()) ;
				if ($display) {
					if (!is_array($this->options['shortcode_auto_fields']) ||
						sizeof($this->options['shortcode_auto_fields'])==0) $fields='';
					else $fields = implode(',', $this->options['shortcode_auto_fields']);

					if ($fields == '') $fields = 'none';

					$attrs = array( 'size'		   => $this->options['shortcode_auto_size'],
									'doctype'  	   => $this->options['shortcode_auto_doc_type'],
									'title'    	   => $this->options['shortcode_auto_title'],
									'titletag'     => $this->options['shortcode_auto_title_tag'],
									'label'    	   => $this->options['shortcode_auto_label'],
									'orderby'      => $this->options['shortcode_auto_orderby'].' '.$this->options['shortcode_auto_order'],
									'fields'	   => $fields,
									'force_saveas' => $this->options['shortcode_auto_force_saveas'],
									'icon'         => $this->options['shortcode_auto_icon']
						);

					$content .= $this->get_attachments($attrs);
				}
			}
			return ($content);
		} /* End of shortcode_auto */
	} /* End of Class */
} /* End of if class_exists */

$eg_attach = new EG_Attachments('EG-Attachments', EG_ATTACH_VERSION, EG_ATTACH_COREFILE);

$eg_attach->set_textdomain('eg-attachments');
$eg_attach->set_stylesheets('eg-attachments.css', FALSE);
$eg_attach->set_owner('Emmanuel GEORJON', 'http://www.emmanuelgeorjon.com/', 'blog@georjon.eu');
$eg_attach->set_wp_versions('2.5',	FALSE, '2.6', FALSE);
$eg_attach->set_options(EG_ATTACH_OPTIONS_ENTRY, $EG_ATTACH_DEFAULT_OPTIONS);
$eg_attach->active_cache(3600);
$eg_attach->load();

?>