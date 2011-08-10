<?php

if (! class_exists('EG_Attachments')) {

	/**
	 * Class EG_Attachments
	 *
	 * Implement a shortcode to display the list of attachments in a post.
	 *
	 * @package EG-Attachments
	 */
	Class EG_Attachments extends EG_Plugin_118 {

		var $icon_height = array( 'large' => 48, 'medium' => 32, 'small' => 16, 'custom' => 48);
		var $icon_width  = array( 'large' => 48, 'medium' => 32, 'small' => 16, 'custom' => 48);
		var $cacheexpiration = 300;

		var $shortcode_exists = FALSE;

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

				add_shortcode(EG_ATTACH_SHORTCODE, array(&$this, 'get_attachments'));
				if ($this->options['shortcode_auto']>0) {
					add_filter('get_the_excerpt', array(&$this, 'shortcode_auto_excerpt'));
					add_filter('the_content',     array(&$this, 'shortcode_auto_content'));
				}
			}
		} /* End of init */

		function prepare_url($url) {

			return esc_url($url, array('http', 'https'));
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
				if ($stats_enable && $this->options['stats_ip_exclude'] != '') {
					$stat_ip = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : FALSE);
					if ($stat_ip !== FALSE ) {
						if (EG_ATTACH_DEBUG_MODE)
							$this->display_debug_info('IP: '.$stat_ip.', exclusion list: '.$this->options['stats_ip_exclude']);
						$stats_enable = (! in_array($stat_ip, explode(',', $this->options['stats_ip_exclude'])) );
					}
				}

				if ($stats_enable) {
					/* Get some details from post parent */
					$post = get_post($_GET['pid']);
					if (! $post || ! in_array($post->post_type, array('post', 'page')))
						return;
					// Count click
					$sql = $wpdb->prepare('SELECT click_id '.
										'FROM '.$wpdb->prefix.'eg_attachments_clicks '.
										'WHERE attach_id=%d '.
										'AND post_id=%d '.
										'AND CURRENT_DATE() = click_date',
										$attach->ID,$post->ID);
					$click_id = $wpdb->get_results($sql);

					if (! $click_id){
						$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'eg_attachments_clicks'.
							' SET attach_id=%d, attach_title="%s", post_id=%d, post_title="%s"'.
							', click_date= CURRENT_DATE(), clicks_number=1',
							$attach->ID, $attach->post_title, $post->ID,$post->post_title);
					}
					else {
						$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'eg_attachments_clicks'.
							' SET clicks_number = clicks_number + 1'.
							' WHERE click_id=%d',$click_id[0]->click_id);
					}
					$wpdb->query($sql);
				} // End of stat enable

				$file_url = wp_get_attachment_url($attach->ID);
				
				if ($_GET['sa'] < 1) {
					// wp_redirect($this->prepare_url($attach->guid));
					if (EG_ATTACH_DEBUG_MODE) $this->display_debug_info('Simple redirect: '.wp_get_attachment_url($attach->ID));
					wp_redirect($this->prepare_url($file_url));
					exit;
				} // End of redirect mode
				else {
					$url = pathinfo($file_url);

					global $is_IE;
					if (strtolower($url['extension']) == 'zip' && $is_IE && ini_get('zlib.output_compression')) {
						ini_set('zlib.output_compression', 'Off');
						// apache_setenv('no-gzip', '1');
					}

					if (EG_ATTACH_DEBUG_MODE) $this->display_debug_info('mime: '.get_post_mime_type($attach->ID));
					if (EG_ATTACH_DEBUG_MODE) $this->display_debug_info('file: '.$this->prepare_url(wp_get_attachment_url($attach->ID)));

					header('Pragma: public');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Cache-Control: private', FALSE); // required for certain browsers
					header('Content-Type: application/force-download');
					header('Content-Type: '.get_post_mime_type($attach->ID), FALSE);
					//header("Content-Type: application/octet-stream", FALSE);
					header("Content-Type: application/download", FALSE);
					header('Content-Disposition: attachment; filename='.$url['basename'].';');
					header('Content-Transfer-Encoding: binary');
					// header('Content-Length: '.filesize($file_path));
					// @readfile($this->prepare_url($attach->guid));
					@readfile($this->prepare_url(wp_get_attachment_url($attach->ID)));
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
				$size_value = explode(' ',size_format($docsize, 0)); // WP function found in file wp-includes/functions.php
				$docsize = $size_value[0].' '.__($size_value[1], $this->textdomain);
			}
			if ($docsize == 0 || $docsize == '') return __('unknown', $this->textdomain);
			else return ($docsize);
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

		/**
		  *  get_type() - Try to get type of document according mime type
		  *
		  * @package EG-Attachments
		  * @param  string 	$mime_type		mime type of the attachment as stored in the DB
		  * @return string					readable type of the attachment
		  */
		function get_type($mime_type) {
			list($part1, $part2) = explode('/', $mime_type);
			switch ($part1) {
				case 'image':
					$attachment_type = $mime_type;
				break;

				case 'application':
					$attachment_type = str_replace('vnd.', '', $part2);
				break;

				default:
					$attachment_type = $part1;
				break;
			} // End of switch
			return ($attachment_type);
		} // End of get_type


		/**
		  *  get_orderby() - return the order sequence for the post query
		  *
		  * @package EG-Attachments
		  * @param 	array 	$args
		  * @return string 			order sequence for post query
		  */
		function get_orderby($args) {
			return ($this->order_by.' '.$this->order);
		} // End of get_orderby

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

			global $EG_ATTACH_FIELDS_ORDER_KEY;
			global $EG_ATTACH_FIELDS_TITLE;
			global $EG_ATTACH_DEFAULT_FIELDS;

			if (function_exists('hidepost_filter_post'))
				global $hidepost_hide_link;
			else
				$hidepost_hide_link = 0;

			add_filter('icon_dirs', array(&$this, 'icon_dirs'));

			extract( shortcode_atts( $EG_ATTACHMENT_SHORTCODE_DEFAULTS, $attr ));

			if ($fields == '' || $fields == 'none') $fields = $EG_ATTACH_DEFAULT_FIELDS[$size];
			else if (! is_array($fields)) $fields = explode(',', $fields);

			if (! is_array($fields)) $fields = $EG_ATTACH_DEFAULT_FIELDS[$size];

			if ($id == 0) $id = $post->ID;
			else $id = intval($id);

			$limit = intval($limit);
			if (! is_int($limit)) $limit = -1;
			elseif ($limit < 0) $limit = -1;

			if ($force_saveas < 0) {
				// Take default options
				$force_saveas = $this->options['force_saveas'];
			}

			if ($logged_users < 0) {
				// Take default options
				$logged_users = $this->options['logged_users_only'];
			}

			if ($display_label < 0) {
				$display_label = $this->options['display_label'];
			}

			list($this->order_by, $this->order) = explode(' ', addslashes($orderby));

			if (isset($EG_ATTACH_FIELDS_ORDER_KEY[strtolower($this->order_by)]))
				$this->order_by = $EG_ATTACH_FIELDS_ORDER_KEY[$this->order_by];

			if (is_string($this->order)) $this->order = strtoupper($this->order);
			if ($this->order == '' || ( ! in_array($this->order, array('ASC', 'DESC')))) $this->order = 'ASC';

			// get attachments
			$attachments = wp_cache_get( 'attachments', 'eg-attachments' );
			$cache_id = $id.'-'.$this->order_by.'-'.$this->order;
			if ($attachments === FALSE || !isset($attachments[$cache_id])) {

				// file wp-includes/query.php, line 2271, only the following order keys are allowed
				// $allowed_keys = array('author', 'date', 'title', 'modified', 'menu_order', 'parent', 'ID', 'rand', 'comment_count');
				// So we need to add our own filter, and the parameter suppress_filters = false
				add_filter('posts_orderby', array(&$this, 'get_orderby') );
				$attachment_list  = get_posts( array('post_parent' => $id,
													 'numberposts' => -1,
													 'post_type'   => 'attachment',
													 'suppress_filters' => false)
											);
				remove_filter('posts_orderby',array(&$this, 'get_orderby') );

				/* is it useful: is cache managed by get_posts? */
				if ($attachment_list !== FALSE && sizeof($attachment_list)>0) {
					$attachments[$cache_id] = $attachment_list;
					wp_cache_set('attachments', $attachments, 'eg-attachments', $this->cacheexpiration);
				}
			}

			// if no attachments, stop and exit
			if ($attachments === FALSE || !isset($attachments[$cache_id])) {
				return '';
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
					$doc_list = explode(',', $docid);
				}
			}

			// Display title
			$output = '';

			if ($size == 'custom') {
			    $format      = ($format==''?$this->options['custom_format']:$format);
			    $format_pre  = ($format_pre==''?$this->options['custom_format_pre']:$format_pre);
				$format_post = ($format_post==''?$this->options['custom_format_post']:$format_post);
			}

			// Display attachment list
			$numberposts = 1;
			foreach ( $attachments[$cache_id] as $attachment ) {
				if (sizeof($doc_list) == 0 || array_search($attachment->ID, $doc_list) !== FALSE) {
					$mime_type = substr($attachment->post_mime_type,0,5);
					if ( $doctype == 'all' ||
					    ($doctype == 'image' && $mime_type == 'image') ||
					    ($doctype == 'document' && $mime_type != 'image') ) {

						$file_size = $this->get_file_size($attachment->ID);
						$fields_value = array(
							'title' 		=> htmlspecialchars(strip_tags($attachment->post_title)),
							'filename'		=> htmlspecialchars(basename(get_attached_file($attachment->ID))),
							'caption' 		=> htmlspecialchars(strip_tags($attachment->post_excerpt)),
							'description' 	=> htmlspecialchars(strip_tags($attachment->post_content)),
							'size' 			=> $file_size,
							'icon'			=> $this->get_icon($attachment->ID, $attachment, $size),
							'date'			=> $attachment->post_date /*mysql2date(get_option('date_format'), $attachment->post_date, TRUE)*/,
							'type'			=> $this->get_type($attachment->post_mime_type)
						);
						$fields_value['label'] = ($label=="filename"?$fields_value['filename']:$fields_value['title']);

						if ($logged_users && ! is_user_logged_in()) {
							$url =  ($this->options['login_url']==''?'#':$this->options['login_url']).
								'"  OnClick="alert(\''.addslashes(__('Attachments restricted to register users only', $this->textdomain)).'\');';
							$lock_icon = '<img class="lock" src="'.$this->plugin_url.'img/lock.png" height="16" width="16" alt="'.__('Document locked', $this->textdomain).'" />';
						}
						else {
							// --- Change in version 1.7.4.1 - Oct 3rd, 2010 ---
							// $query = parse_url(get_permalink(), PHP_URL_QUERY);
							$lock_icon = '';
							$query = parse_url(get_permalink());

							$query['query'] = (isset($query['query'])&& $query['query']!='' ? $query['query'].'&amp;' : '' ).
												http_build_query( array( 'aid' => $attachment->ID,
																		 'pid' => $id,
																		 'sa'  => $force_saveas ));
							$url = $query['scheme'].'://'.
									(isset($query['user'])?$query['user'].
									(isset($query['password'])?':'.$query['password']:'').'@':'').
									(isset($query['host'])?$query['host']:'').
									(isset($query['path'])?$query['path']:'').
									'?'.
							        $query['query'].
									(isset($query['anchor'])?'#'.$query['anchor']:'');
/*
							$url = $query['scheme'].'://'.
									(isset($query['user'])?$query['user'].
									(isset($query['password'])?':'.$query['password']:'').'@':'').
									(isset($query['host'])?$query['host']:'').
									(isset($query['path'])?$query['path']:'').
									'?'.
							        (isset($query['query'])&& $query['query']!='' ? $query['query'].'&amp;' : '' ) .
								   	'aid='.$attachment->ID.'&amp;pid='.$id.'&amp;sa='.$force_saveas.
									(isset($query['anchor'])?'#'.$query['anchor']:'');
*/
						}
						$link = '<a title="'.$fields_value['title'].'" href="'.$url.'" '.($this->options['nofollow']?'rel="nofollow"':'').'>';

						switch ($size) {

							case 'custom':  //Functionality added by Jxs / www.jxs.nl
								$tmp = html_entity_decode (stripslashes($format));
								$tmp = preg_replace("/%URL%/",        ($hidepost_hide_link==1?'#':$url),$tmp);
								//A direct link to some files may be necessary - some programs don't work when the mimetype is not set correctly
								$tmp = preg_replace("/%GUID%/",        $attachment->guid,			$tmp);
								$tmp = preg_replace("/%ICONURL%/",     $fields_value['icon'],		$tmp);
								$tmp = preg_replace("/%TITLE%/",       $fields_value['title'],		$tmp);
								$tmp = preg_replace("/%LABEL%/",       $fields_value['label'],		$tmp);
								$tmp = preg_replace("/%CAPTION%/",     $fields_value['caption'],	$tmp);
								$tmp = preg_replace("/%DESCRIPTION%/", $fields_value['description'],$tmp);
								$tmp = preg_replace("/%FILENAME%/",    $fields_value['filename'],	$tmp);
								$tmp = preg_replace("/%FILESIZE%/",    $fields_value['size'],		$tmp);
								$tmp = preg_replace("/%ATTID%/",       $attachment->ID,				$tmp); //For use with stylesheets
								$tmp = preg_replace("/%TYPE%/",		   strtoupper($fields_value['type']),$tmp);

								$output .= $tmp;
								if(strlen($format)) break; //If !strlen continue in 'large'

							case 'large':
							case 'medium':
							case 'small':
								$caption_list = array();
								$first_field = TRUE;
								foreach ($fields as $field) {
									if (isset($EG_ATTACH_FIELDS_TITLE[$field])) {
										if ($field == 'small_size')
											$caption_list[max(sizeof($caption_list)-1,0)] .= ' ('.$file_size.')';
										else {
											$caption_list[] = (($size!='small' || $display_label)?'<strong>'.__($EG_ATTACH_FIELDS_TITLE[$field], $this->textdomain).'</strong> : ':'').
											($first_field?$link:'').$fields_value[$field].($first_field?'</a>':'');
										}
										$first_field=FALSE;
									}
								} // End of foreach
								if ($icon) {
									$output .= '<dl class="attachments attachments-'.$size.'"><dt class="icon">'.
										($hidepost_hide_link==1?'':$link).$fields_value['icon'].
										($hidepost_hide_link==1?'':'</a>').'</dt>'.
										'<dd class="caption">'.$lock_icon.
										implode('<br />', $caption_list).
										'</dd>'.
										'</dl>';
								}
								else {
									$output .= '<li class="attachments attachments-'.$size.'">'.
										implode('<br />', $caption_list).
										'</li>';
								}
							break;
						} // End of switch size
						$numberposts++;
						if ($limit>0 && $numberposts > $limit) break;
					} // End of document type
				} // End of doc_list
			} // End of foreach attachment

			$output = trim($output);

			if ($output != '') {
				if ($size == 'custom' && $format_post != '')
					$output = html_entity_decode (stripslashes($format_pre)).$output.html_entity_decode (stripslashes($format_post));
				else {
					if (! $icon)
						$output = '<ul>'.$output.'</ul>';
				}
				if ( $title != '') {
					$output = '<'.$titletag.'>'.htmlspecialchars(stripslashes(strip_tags($title))).'</'.$titletag.'>'.$output;
				}
				$output = '<div class="attachments">'.$output.'</div>';
				if (function_exists('hidepost_filter_post')) $output = hidepost_filter_post($output);

			} // End of $output

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
		 * shortcode_auto_check_manual_shortcode
		 *
		 * Detect manual shortcode
		 *
		 * @return  int		1 if a shortcode is detected, 0 if not
		 */
		function shortcode_auto_check_manual_shortcode() {
			global $post;

			if ($this->shortcode_exists === FALSE) {
				if ($this->options['shortcode_auto_exclusive']==0) $this->shortcode_exists = 0;
				else $this->shortcode_exists = (strpos($post->post_excerpt.' '.$post->post_content, '['.EG_ATTACH_SHORTCODE.' ') !== FALSE);
			}
			return ($this->shortcode_exists);
		} // End of shortcode_auto_check_manual_shortcode

		/**
		 * shortcode_auto_excerpt
		 *
		 * Display list of attachment in the post excerpt
		 *
		 * @return 	string				modified post content
		 */
		function shortcode_auto_excerpt($output) {

			if ($output && $this->options['shortcode_auto'] == 3 &&
			    $this->shortcode_is_visible() &&
				! $this->shortcode_auto_check_manual_shortcode()) {

				if ($this->options['shortcode_auto_fields_def']) $fields = '';
				else $fields = $this->options['shortcode_auto_fields'];

				$attrs = array( 'size'	   => $this->options['shortcode_auto_size'],
						'doctype'  	   => $this->options['shortcode_auto_doc_type'],
						'title'    	   => $this->options['shortcode_auto_title'],
						'titletag'     => $this->options['shortcode_auto_title_tag'],
						'label'    	   => $this->options['shortcode_auto_label'],
						'orderby'      => $this->options['shortcode_auto_orderby'].' '.$this->options['shortcode_auto_order'],
						'fields'	   => $fields,
						'force_saveas' => $this->options['force_saveas'],
						'icon'         => $this->options['shortcode_auto_icon'],
						'logged_users' => $this->options['logged_users_only'],
						'format'       => $this->options['custom_format'     ],
						'format_pre'   => $this->options['custom_format_pre' ],
						'format_post'  => $this->options['custom_format_post']
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
			global $EG_ATTACH_DEFAULT_FIELDS;
			global $post;

			if ($this->options['shortcode_auto']  > 0 	&&
				/* $this->options['shortcode_auto'] != 3 	&& */
				$this->shortcode_is_visible() 			&&
				! $this->shortcode_auto_check_manual_shortcode()) {

				if ($this->options['shortcode_auto_fields_def']) $fields = '';
				else $fields = $this->options['shortcode_auto_fields'];

				$attrs = array( 'size'		   => $this->options['shortcode_auto_size'],
								'doctype'  	   => $this->options['shortcode_auto_doc_type'],
								'title'    	   => $this->options['shortcode_auto_title'],
								'titletag'     => $this->options['shortcode_auto_title_tag'],
								'label'    	   => $this->options['shortcode_auto_label'],
								'orderby'      => $this->options['shortcode_auto_orderby'].' '.$this->options['shortcode_auto_order'],
								'fields'	   => $fields,
								'force_saveas' => $this->options['force_saveas'],
								'icon'         => $this->options['shortcode_auto_icon'],
								'logged_users' => $this->options['logged_users_only'],
								'format'       => $this->options['custom_format'     ],
								'format_pre'   => $this->options['custom_format_pre' ],
								'format_post'  => $this->options['custom_format_post'],
								'limit'		   => $this->options['shortcode_auto_limit']
					);
				$shortcode_output = $this->get_attachments($attrs);

				switch ($this->options['shortcode_auto']) {
					case 2: // At the end of post
						$content .= $shortcode_output;
					break;

					case 3: // Before the excerpt
						if (! $post->post_excerpt)
							$content = $shortcode_output . $content;
					break;

					case 4:
						if ($post->post_excerpt) {
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
$eg_attach->set_wp_versions('2.9',	FALSE, '2.9', FALSE);

if (EG_ATTACH_DEBUG_MODE)
	$eg_attach->set_debug_mode(TRUE, 'debug.log');

$eg_attach->load();

?>