<?php

if (! class_exists('EG_Attachments')) {

	/**
	 * Class EG_Attachments
	 *
	 * Implement a shortcode to display the list of attachments in a post.
	 *
	 * @package EG-Attachments
	 */
	Class EG_Attachments extends EG_Plugin_114 {

		var $icon_height = array( 'large' => 48, 'medium' => 32, 'small' => 16, 'custom' => 48);
		var $icon_width  = array( 'large' => 48, 'medium' => 32, 'small' => 16, 'custom' => 48);
		var $cacheexpiration = 300;

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

			$this->manage_link();

			parent::init();

			if (! is_admin()) {

				add_shortcode('attachments', array(&$this, 'get_attachments'));
				if ($this->options['shortcode_auto']>0) {
					add_filter('get_the_excerpt', array(&$this, 'shortcode_auto_excerpt'));
					add_filter('the_content',     array(&$this, 'shortcode_auto_content'));
				}
			}
		} /* End of init */

		function prepare_url($url) {

			return esc_url_raw($url, array('http', 'https'));
		}

		/**
		 * manage_link
		 *
		 * Add filter, hooks or action.
		 *
		 * @package EG-Attachments
		 * @param none
		 * @return none
		 */
		function manage_link() {
			global $wpdb;

			if (isset($_GET['aid']) && isset($_GET['pid']) && isset($_GET['sa']) &&
				is_numeric($_GET['aid']) && is_numeric($_GET['pid']) && is_numeric($_GET['sa']) ) {

				$attach = get_post($_GET['aid']);
				if (! $attach || $attach->post_type != 'attachment')
					return;

				$stats_enable = ($this->options['stats_enable'] && $this->options['clicks_table']);
				if ($stats_enable) {
					$stat_ip = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : FALSE);
					if ($stat_ip !== FALSE && $this->options['stats_ip_exclude'] != '')
						$stats_enable = ( in_array($stat_ip, explode(',', $this->options['stats_ip_exclude'])) );
				}

				if ($stats_enable) {
					$post = get_post($_GET['pid']);
					if (! $post || ! in_array($post->post_type, array('post', 'page')))
						return;

					// Count click
					$sql = 'SELECT click_id FROM '.$wpdb->prefix.'eg_attachments_clicks '.
							'WHERE attach_id='.$attach->ID.' AND post_id='.$post->ID.' AND CURRENT_DATE() = click_date';

					$click_id = $wpdb->get_results($sql);

					if (! $click_id){
						$sql = 'INSERT INTO '.$wpdb->prefix.'eg_attachments_clicks'.
							' SET attach_id='.$attach->ID.', attach_title="'.$attach->post_title.'"'.
							', post_id='.$post->ID.', post_title="'.$post->post_title.'"'.
							', click_date= CURRENT_DATE()'.
							', clicks_number=1';
					}
					else {
						$sql = 'UPDATE '.$wpdb->prefix.'eg_attachments_clicks'.
							' SET clicks_number = clicks_number + 1'.
							' WHERE click_id='.$click_id[0]->click_id;
					}
					$wpdb->query($sql);
				} // End of stat enable

				if ($_GET['sa'] < 1) {
					wp_redirect($this->prepare_url($attach->guid));
					exit;
				} // End of redirect mode
				else {
					$url = pathinfo($attach->guid);
					header("Pragma: public");
					header("Expires: 0");
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header("Content-Type: application/force-download");
					header("Content-Type: application/octet-stream");
					header("Content-Type: application/download");
					header("Content-Disposition: attachment; filename=".$url['basename'].";");
					header("Content-Transfer-Encoding: binary");
					// header("Content-Length: ".filesize($file_path));
					@readfile($this->prepare_url($attach->guid));
					exit;
				} // End of Download mode
			} // End of parameters OK
		} // End of manage_link

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
		  * @param  int     $file_id    id of attachment to get size
		  * @return	float 				size of the attachment
		  */
		function get_file_size($file_id) {

			// Get the path of the file
			$file_path = get_attached_file($file_id);

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
			$description = ($attachment->post_content!=''?$attachment->post_content:($attachment->post_title!=''?$attachment->post_title:$attachment->post_name));
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
			global $post;
			global $EG_ATTACHMENT_SHORTCODE_DEFAULTS;

			if (function_exists('hidepost_filter_post'))
				global $hidepost_hide_link;
			else
				$hidepost_hide_link = 0;

			add_filter('icon_dirs', array(&$this, 'icon_dirs'));

			// Preparing parameters and query
			//$EG_ATTACHMENT_SHORTCODE_DEFAULTS['id'] = $post->ID;
			extract( shortcode_atts( $EG_ATTACHMENT_SHORTCODE_DEFAULTS, $attr ));

			if ($id == 0) $id = $post->ID;
			else $id = intval($id);

			$orderby = addslashes($orderby);

			if ($force_saveas < 0) {
				// Take default options
				$force_saveas = $this->options['force_saveas'];
			}
			if ($logged_users < 0) {
				// Take default options
				$logged_users = $this->options['logged_users_only'];
			}

			list($order_by, $order) = split(' ', $orderby);
			if ($order == '') $order = 'ASC';

			// get attachments
			$attachments = wp_cache_get( 'attachments', 'eg-attachments' );
			if ($attachments === FALSE || !isset($attachments[$id])) {

				$attachment_list  = get_posts( array('post_parent' => $id,
													'numberposts'	=> -1,
													'post_type'		=> 'attachment',
													'orderby'		=> $order_by,
													'order'			=> $order
												)
											);
				if ($attachment_list !== FALSE && sizeof($attachment_list)>0) {
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

			if ($size == 'custom') {
			    $format      = ($format==''?$this->options['custom_format']:$format);
			    $format_pre  = ($format_pre==''?$this->options['custom_format_pre']:$format_pre);
				$format_post = ($format_post==''?$this->options['custom_format_post']:$format_post);
				$output = html_entity_decode (stripslashes($format_pre));
			}
			// Display attachment list
			foreach ( $attachments[$id] as $attachment ) {
				if (sizeof($doc_list) == 0 || array_search($attachment->ID, $doc_list) !== FALSE) {
					$mime_type = substr($attachment->post_mime_type,0,5);
					if ( $doctype == 'all' ||
					    ($doctype == 'image' && $mime_type == 'image') ||
					    ($doctype == 'document' && $mime_type != 'image') ) {

						$file_size = $this->get_file_size(/* $attachment->guid */ $attachment->ID);
						$attachment_title = htmlspecialchars(strip_tags($attachment->post_title));

						if ($logged_users && ! is_user_logged_in()) {
							$url = ($this->options['login_url']==''?'#':$this->options['login_url']).
								'"  OnClick="alert(\''.addslashes(__('Attachments restricted to register users only', $this->textdomain)).'\');';
							$link = '<a title="'.$attachment_title.'" href="'.$url.'">';
							$lock_icon = '<img class="lock" src="'.$this->plugin_url.'img/lock.png" height="16" width="16" alt="'.__('Document locked', $this->textdomain).'" />';
						}
						else {
							$query = parse_url(get_permalink(), PHP_URL_QUERY);
							$url = get_permalink() .
							       ($query == '' ? '?' : '&amp;') .
								   	'aid='.$attachment->ID.'&amp;pid='.$id.'&amp;sa='.$force_saveas;

							$link = '<a title="'.$attachment_title.'" href="'.$url.'">';
							$lock_icon = '';
						}
						switch ($size) {

							case 'custom':  //Functionality added by Jxs / www.jxs.nl
								$tmp = html_entity_decode (stripslashes($format));
								$tmp = preg_replace("/%URL%/",        ($hidepost_hide_link==1?'#':$url),$tmp);
								//A direct link to some files may be necessary - some programs don't work when the mimetype is not set correctly
								$tmp = preg_replace("/%GUID%/",        $attachment->guid,$tmp);
								$tmp = preg_replace("/%ICONURL%/",     $this->get_icon($attachment->ID, $attachment, $size),$tmp);
								$tmp = preg_replace("/%TITLE%/",       $attachment_title,$tmp);
								$tmp = preg_replace("/%CAPTION%/",     $attachment->post_excerpt,$tmp);
								$tmp = preg_replace("/%DESCRIPTION%/", $attachment->post_content,$tmp);
								$tmp = preg_replace("/%FILENAME%/",    basename($attachment->guid),$tmp);
								$tmp = preg_replace("/%FILESIZE%/",    $file_size,$tmp);
								$tmp = preg_replace("/%ATTID%/",       $attachment->ID,$tmp); //For use with stylesheets
								$output .= $tmp;
								if(strlen($format)) break; //If !strlen continue in 'large'

							case 'large':
								if ($file_size != '') $string_file_size = '<strong>'.__('Size: ', $this->textdomain).'</strong>'.$file_size;
								else $string_file_size = '';
								if ($icon) {
									$output .= '<dl class="attachments attachments-large"><dt class="icon">'.
										($hidepost_hide_link==1?'':$link).$this->get_icon($attachment->ID, $attachment, $size).
										($hidepost_hide_link==1?'':'</a>').'</dt>'.
										'<dd class="caption">'.$lock_icon.'<strong>'.
										__('Title: ', $this->textdomain).'</strong>'.$link.$attachment_title.
										'</a><br />'.
										(($attachment->post_excerpt==''||strpos($fields,'caption')===FALSE)?'':'<strong>'.
										__('Caption: ', $this->textdomain).'</strong>'.$attachment->post_excerpt.'<br />').
										(($attachment->post_content==''||strpos($fields,'description')===FALSE)?'':'<strong>'.
										__('Description: ', $this->textdomain).'</strong>'.$attachment->post_content.'<br />').
										'<strong>'.__('File: ', $this->textdomain).'</strong>'.basename($attachment->guid).'<br />'.
										$string_file_size.
										'</dd>'.
										'</dl>';
								}
								else {
									$output .= '<li class="attachments attachments-large">'.
										'<strong>'.__('Title: ', $this->textdomain).'</strong>'.
										$link.$attachment_title.'</a><br />'.
										(($attachment->post_excerpt==''||strpos($fields,'caption')===FALSE)?'':'<strong>'.
										__('Caption: ', $this->textdomain).'</strong>'.$attachment->post_excerpt.'<br />').
										(($attachment->post_content==''||strpos($fields,'description')===FALSE)?'':'<strong>'.
										__('Description: ', $this->textdomain).'</strong>'.$attachment->post_content.'<br />').
										'<strong>'.__('File: ', $this->textdomain).'</strong>'.basename($attachment->guid).'<br />'.
										$string_file_size.
										'</li>';
								}
							break;

							case 'medium':
								if ($file_size != '') $string_file_size = '('.$file_size.')';
								else $string_file_size = '';
								if ($icon) {
									$output .= '<dl class="attachments attachments-medium">'.
										'<dt class="icon">'.($hidepost_hide_link==1?'':$link).
										$this->get_icon($attachment->ID, $attachment, $size).($hidepost_hide_link==1?'':'</a>').
										'</dt>'.
										'<dd class="caption"><strong>';
									if  ($label == 'doctitle') {
										$output .= __('Title: ', $this->textdomain).'</strong>'.
											$link.$attachment_title.'</a> '.
											$string_file_size.'<br />';
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
								else $string_file_size = '';
								if ($icon) {
									$output .= '<dl class="attachments attachments-small"><dt class="icon">'.
								        ($hidepost_hide_link==1?'':$link).
										$this->get_icon($attachment->ID, $attachment, $size).
										($hidepost_hide_link==1?'':'</a>').
										'</dt>'.
										'<dd class="caption">'.
										$link.
										($label=="doctitle"?$attachment_title:basename($attachment->guid)).
										'</a> '.
										$string_file_size.
										'</dd></dl>';
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

			if ($output != '') {
				if ($size == 'custom' && $format_post!='')
					$output .= html_entity_decode (stripslashes($format_post));
				else {
					if (! $icon)
						$output = '<ul>'.$output.'</ul>';
				}
			}

			if ($output != '' && $title != '') {
				$output = '<'.$titletag.'>'.htmlspecialchars(stripslashes(strip_tags($title))).'</'.$titletag.'>'.$output;
			}

			if ($output != '') {
				$output = '<div class="attachments">'.$output.'</div>';
			}

			if (function_exists('hidepost_filter_post')) $output = hidepost_filter_post($output);

			remove_filter('icon_dirs', array(&$this, 'icon_dirs'));

			return $output;
		} // End of get_attachments


		/**
		 * shortcode_is_visible
		 *
		 * Define if the auto shortcode is visible or not
		 *
		 * @return 	boolean		TRUE if shortcode is visible, FALSE if not
		 */
		function shortcode_is_visible() {
			global $post;
			return (!post_password_required($post) &&
			        ( $this->options['shortcode_auto_where'] != 'post' || is_single() || is_page() )
				);
		} // End of shortcode_is_visible

		/**
		 * shortcode_auto_excerpt
		 *
		 * Display list of attachment in the post excerpt
		 *
		 * @return 	string				modified post content
		 */
		function shortcode_auto_excerpt($output) {

			if ($output && $this->options['shortcode_auto'] == 3 && $this->shortcode_is_visible() ) {
				$attrs = array( 'size'	   => $this->options['shortcode_auto_size'],
							'doctype'  	   => $this->options['shortcode_auto_doc_type'],
							'title'    	   => $this->options['shortcode_auto_title'],
							'titletag'     => $this->options['shortcode_auto_title_tag'],
							'label'    	   => $this->options['shortcode_auto_label'],
							'orderby'      => $this->options['shortcode_auto_orderby'].' '.$this->options['shortcode_auto_order'],
							'fields'	   => $fields,
							'force_saveas' => $this->options['force_saveas'],
							'icon'         => $this->options['shortcode_auto_icon'],
							'logged_users' => $this->options['logged_users'],
							'format'       => $this->options['shortcode_auto_format'     ],
							'format_pre'   => $this->options['shortcode_auto_format_pre' ],
							'format_post'  => $this->options['shortcode_auto_format_post']
				);
				$output = $this->get_attachments($attrs).$output;
			} // End of shortcode activated and visible
			return ($output);
		} // End of shortcode_auto_excerpt


		/**
		 * shortcode_auto_content
		 *
		 * Display list of attachment in the post content
		 *
		 * @param 	strong	$content	post_content
		 * @return 	string				modified post content
		 */
		function shortcode_auto_content($content = '') {
			global $post;

			if ($this->options['shortcode_auto'] > 0 && $this->shortcode_is_visible() ) {

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
								'force_saveas' => $this->options['force_saveas'],
								'icon'         => $this->options['shortcode_auto_icon'],
								'logged_users' => $this->options['logged_users'],
								'format'       => $this->options['shortcode_auto_format'     ],
								'format_pre'   => $this->options['shortcode_auto_format_pre' ],
								'format_post'  => $this->options['shortcode_auto_format_post']
					);
				$shortcode_output = $this->get_attachments($attrs);

				switch ($this->options['shortcode_auto']) {
					case 2: // At the end of post
						$content .= $shortcode_output;
					break;

					case 3: // Before the excerpt
						if (! $post_excerpt)
							$content = $shortcode_output . $content;
					break;

					case 4:
						if ($post_excerpt) {
							// Case of manual excerpt
							$content = $shortcode_output . $content;
						}
						else {
							// Case of teaser
							if(strpos($content, 'span id="more-')) {
								$parts = preg_split('/(<span id="more-[0-9]*"><\/span>)/', $content, -1,  PREG_SPLIT_DELIM_CAPTURE);
								$content = $parts[0].$parts[1].$shortcode_output.$parts[2];
							} // End of detect tag "more"
						} // End of teaser case
					break;
				} // End of switch
			} // End of shortcode is activated and visible
			return ($content);
		} /* End of shortcode_auto_content */

	} /* End of Class */
} /* End of if class_exists */

$eg_attach = new EG_Attachments('EG-Attachments', EG_ATTACH_VERSION, EG_ATTACH_COREFILE, EG_ATTACH_OPTIONS_ENTRY, $EG_ATTACH_DEFAULT_OPTIONS);

$eg_attach->set_textdomain(EG_ATTACH_TEXTDOMAIN);
$eg_attach->set_stylesheets('eg-attachments.css', FALSE);
$eg_attach->set_wp_versions('2.8',	FALSE, '2.8', FALSE);
$eg_attach->load();

?>