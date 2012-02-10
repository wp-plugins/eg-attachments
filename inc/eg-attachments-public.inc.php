<?php

if (! class_exists('EG_Attachments')) {

	/**
	 * Class EG_Attachments
	 *
	 * Implement a shortcode to display the list of attachments in a post.
	 *
	 * @package EG-Attachments
	 */
	Class EG_Attachments extends EG_Plugin_126 {

		var $icon_height = array( 'large' => 48, 'medium' => 32, 'small' => 16);
		var $icon_width  = array( 'large' => 48, 'medium' => 32, 'small' => 16);
		var $attachments = FALSE;
		var $shortcode_exists = array();

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

			add_action('template_redirect', array(&$this, 'manage_link'));

			parent::init();

			add_shortcode(EGA_SHORTCODE, array(&$this, 'get_attachments'));
			/* && $this->shortcode_is_visible(): cannot put this function here. wp_query object is not ready */
			if ($this->options['shortcode_auto']>0  ) {
				add_filter('get_the_excerpt', array(&$this, 'shortcode_auto_excerpt'));
				add_filter('the_content',     array(&$this, 'shortcode_auto_content'));
			}

			global $wp_version;
			if (isset($this->options['display_admin_bar']) && $this->options['display_admin_bar']) {
				if (version_compare($wp_version, '3.2.99', '>') )
					add_action( 'admin_bar_menu',  'eg_attachments_custom_admin_bar', 99 );
				else if (version_compare($wp_version, '3.1.99', '>'))
					add_action( 'wp_before_admin_bar_render', 'eg_eg_attachment_custom_admin_bar' );
			}
		} /* End of init */

		/**
		 * Encode_url
		 *
		 * Add filter, hooks or action.
		 *
		 * @package EG-Attachments
		 * @param none
		 * @return none
		 */
		function encode_url($url) {
			$url = parse_url($url);

			// Split query part
			$query_params = array();
			if (isset($url['query'])) {
				$params = explode('&',$url['query']);
				foreach ($params as $param) {
					list($key, $value) = explode('=', $param);
					$query_params[] = rawurlencode(utf8_encode($key)).'='.rawurlencode(utf8_encode($value));
				}
			}

			$new_url = $url['scheme'].'://'.
						(isset($url['user'])?$url['user'].(isset($url['pass'])?':'.$url['pass']:'').'@':'').
						$url['host'].
						(isset($url['path'])?str_replace('%2F','/', rawurlencode(utf8_encode($url['path']))):'').
						(sizeof($query_params)>0?'?'.implode('&',$query_param):'').
						(isset($url['fragment'])?'#'.$url['fragment']:'');

			return htmlspecialchars($new_url);
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
			global $post;

			// Be sure that the link is coming from EG-Attachments
			if ( isset($_GET['aid']) && is_numeric($_GET['aid']) &&
				 isset($_GET['sa']) && is_numeric($_GET['sa']) ) {

				// First security check. If post not defined, potential hack tentative.
				if (! isset($post))
					wp_die(__('Something is going wrong. Bad address, or perhaps you try to access to a private document.', $this->textdomain));

				$attach = get_post($_GET['aid']);
				if (isset($attach) && $attach && $attach->post_type=='attachment')
					$attach_id = $attach->ID;

				if ( isset($attach_id) ) {

					$parent_id = reset(get_post_ancestors($attach_id));

					// Second security check: private posts / pages
					if ('private' == get_post_field('post_status', $parent_id) && !is_user_logged_in()) {
						wp_die(__('This post is private. You must be a user of the site, and logged in, to display this file.', $this->textdomain));
					}

					// Third security check: protected post
					if (post_password_required($parent_id)) {
						wp_die(__('This post is password protected. Please go to the site, and enter the password required to display the document', $this->textdomain));
					}

					$stats_enable = ($this->options['stats_enable'] && $this->options['clicks_table']);
					if ($stats_enable && $this->options['stats_ip_exclude'] != '') {
						$stat_ip = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : FALSE);
						if ($stat_ip !== FALSE ) {
							if (EGA_DEBUG_MODE)
								$this->display_debug_info('IP: '.$stat_ip.', exclusion list: '.$this->options['stats_ip_exclude']);
							$stats_enable = (! in_array($stat_ip, explode(',', $this->options['stats_ip_exclude'])) );
						}
					}

					if ($stats_enable) {
						/* Get some details from post parent */
						$post = get_post($parent_id);
						if (! $post || ! in_array($post->post_type, array('post', 'page')))
							return;
						// Count click
						$sql = $wpdb->prepare('SELECT click_id '.
											'FROM '.$wpdb->prefix.'eg_attachments_clicks '.
											'WHERE attach_id=%d '.
											'AND post_id=%d '.
											'AND CURRENT_DATE() = click_date',
											$attach->ID,$parent_id);
						$click_id = $wpdb->get_results($sql);

						if (! $click_id){
							$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'eg_attachments_clicks'.
								' SET attach_id=%d, attach_title="%s", post_id=%d, post_title="%s"'.
								', click_date= CURRENT_DATE(), clicks_number=1',
								$attach_id, $attach->post_title, $post->ID,$post->post_title);
						}
						else {
							$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'eg_attachments_clicks'.
								' SET clicks_number = clicks_number + 1'.
								' WHERE click_id=%d',$click_id[0]->click_id);
						}
						$wpdb->query($sql);
					} // End of stat enable


					if ($_GET['sa'] < 1) {
						if (!is_attachment()) {
							wp_redirect($this->encode_url(wp_get_attachment_url($attach_id)));
							exit;
						}
					}
					else { // Force "Save as"

						$file_path = get_attached_file($attach_id);
						$file_size = @filesize($file_path);

						global $is_IE;
						$path = pathinfo($file_path);

						if (isset($path['extension']) && strtolower($path['extension']) == 'zip' &&
							$is_IE && ini_get('zlib.output_compression')) {
							ini_set('zlib.output_compression', 'Off');
							// apache_setenv('no-gzip', '1');
						}

						header('Pragma: public');
						header('Expires: 0');
						header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						header('Cache-Control: private', FALSE);
						header('Content-Type: application/force-download', FALSE);
						header('Content-Type: application/octet-stream', FALSE);
						header('Content-Type: application/download', FALSE);
						header('Content-Disposition: attachment; filename="'.basename($file_path).'";');
						header('Content-Transfer-Encoding: binary');
						if ($file_size!==FALSE) header('Content-Length: '.$file_size);
						@readfile($file_path);
						exit();
					} // End of force save as

				} // End of $attach_id exists

			} // End of parameters exist (aid, and sa)
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
				//$new_args = array_merge(array($this->path.'img/flags' => $this->url.'img/flags'),$args);
				if ($this->options['icon_path']!='' && 
					$this->options['icon_url']!='' &&
					file_exists(str_replace('\\','/',trailingslashit(ABSPATH).$this->options['icon_path']))) {
					$new_args = array_merge(array($this->path.'img/flags' => $this->url.'img/flags'),
										array(str_replace('\\','/',trailingslashit(ABSPATH).$this->options['icon_path']) => trailingslashit(get_bloginfo('home')).$this->options['icon_url']),
										$args);				
				}
				else {
					$new_args = array_merge(array($this->path.'img/flags' => $this->url.'img/flags'),$args);
				}
			}
			return ($new_args);
		} // End of icon_dirs

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

			if ($size == 'custom') {
				$width  = $this->options['custom_format_icon_width'];
				$height = $this->options['custom_format_icon_height'];
			}
			else {
				$width  = $this->icon_width[$size];
				$height = $this->icon_height[$size];
			}
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
		  *  sort_attachments
		  *
		  * @package EG-Attachments
		  * @param 	object		$a, $b
		  * @return boolean		1 if $a>$b, -1 if $a<$b
		  */
		//function sort_attachments($a, $a) {
		//	return ($this->order_by.' '.$this->order);
		//} // End of sort_attachments

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

			$EG_ATTACHMENT_SHORTCODE_DEFAULTS['id'] 			= $post->ID;
			$EG_ATTACHMENT_SHORTCODE_DEFAULTS['force_saveas'] 	= $this->options['force_saveas'];
			$EG_ATTACHMENT_SHORTCODE_DEFAULTS['nofollow'] 		= $this->options['nofollow'];
			$EG_ATTACHMENT_SHORTCODE_DEFAULTS['target'] 		= $this->options['target_blank'];
			$EG_ATTACHMENT_SHORTCODE_DEFAULTS['logged_users'] 	= $this->options['logged_users_only'];
			$EG_ATTACHMENT_SHORTCODE_DEFAULTS['login_url'] 		= $this->options['login_url'];
			$EG_ATTACHMENT_SHORTCODE_DEFAULTS['display_label'] 	= $this->options['display_label'];
			$EG_ATTACHMENT_SHORTCODE_DEFAULTS['format'] 		= $this->options['custom_format'];
			$EG_ATTACHMENT_SHORTCODE_DEFAULTS['format_pre'] 	= $this->options['custom_format_pre'];
			$EG_ATTACHMENT_SHORTCODE_DEFAULTS['format_post'] 	= $this->options['custom_format_post'];

			extract( shortcode_atts( $EG_ATTACHMENT_SHORTCODE_DEFAULTS, $attr ));

			list($this->order_by, $this->order) = explode(' ', $orderby);

			if (! isset($EG_ATTACH_FIELDS_ORDER_KEY[strtolower($this->order_by)]))
				$this->order_by = reset(explode(' ',$EG_ATTACHMENT_SHORTCODE_DEFAULTS['orderby'] ));
			$this->order_by = $EG_ATTACH_FIELDS_ORDER_KEY[strtolower($this->order_by)];

			// get attachments
			$cache_id = $id.'-'.$this->order_by.'-'.$this->order;
			if ($this->attachments === FALSE || !isset($this->attachments[$cache_id])) {

				// file wp-includes/query.php, line 2271, only the following order keys are allowed
				// $allowed_keys = array('author', 'date', 'title', 'modified', 'menu_order', 'parent', 'ID', 'rand', 'comment_count');
				// So we need to add our own filter, and the parameter suppress_filters = false
				//add_filter('posts_orderby', array(&$this, 'get_orderby') );

				$params = array('post_parent' => $id,
								'numberposts' => -1,
								'post_type'   => 'attachment',
								'orderby'	  => $this->order_by,
								'order'	   	  => $this->order);

				if (isset($tags) && $tags != '') {
					$list = explode(',', $tags);
					if (! is_array($list)) $params['tag'] = $list;
					else {
						if (sizeof($list) == 1) $params['tag'] = current($list);
						$params['tag_slug__in'] = $list;
					}
				}

				$this->attachments[$cache_id] = get_posts( $params );

				//remove_filter('posts_orderby',array(&$this, 'get_orderby') );
			}

			// if no attachments, stop and exit
			if ($this->attachments === FALSE || !isset($this->attachments[$cache_id]) || sizeof($this->attachments[$cache_id])==0) {
				return '';
			}

			$wordpress_sort_keys = array('author' 	=> 'author',
										'date'		=> 'date',
										'title'		=> 'title',
										'modified'	=> 'modified',
										'menu_order'=> 'menu_order',
										'parent'	=> 'parent',
										'ID'		=> 'ID',
										'rand'		=> 'rand',
										'comment_count' => 'comment_count');

			if (!isset($wordpress_sort_keys[$this->order_by])) {
			//file_put_contents(dirname(__FILE__).'/debug.log', var_export($this->attachments[$cache_id], TRUE), FILE_APPEND);
				$compare = ($this->order === 'ASC')
							? 'return strcmp($a->'.$this->order_by.', $b->'.$this->order_by.');'
							: 'return -strcmp($a->'.$this->order_by.', $b->'.$this->order_by.');';
				uasort($this->attachments[$cache_id], create_function('$a,$b', $compare));
			}

			if (function_exists('hidepost_filter_post'))
				global $hidepost_hide_link;
			else
				$hidepost_hide_link = 0;

			if ($size == 'custom') $fields = array();
			else {
				if ($fields == '' || $fields == 'none') $fields = $EG_ATTACH_DEFAULT_FIELDS[$size];
				else if (! is_array($fields)) $fields = explode(',', $fields);

				if (! is_array($fields)) $fields = $EG_ATTACH_DEFAULT_FIELDS[$size];
			}

			if ($docid == '0') {
				$doc_list = array();
			}
			else {
				if ($docid == 'first') {
					$temp = reset($this->attachments[$cache_id]);
					$doc_list = array( $temp->ID );
				}
				elseif ($docid == 'last') {
					$temp = end($this->attachments[$cache_id]);
					$doc_list = array( $temp->ID );
				}
				else {
					$doc_list = explode(',', $docid);
				}
			}

			$date_format = ($this->options['date_format']!=''?$this->options['date_format']:get_option('date_format'));

			// Display title
			$output = '';

			// Display attachment list
			$numberposts = 1;
			add_filter('icon_dirs', array(&$this, 'icon_dirs'));
			foreach ( $this->attachments[$cache_id] as $attachment ) {
				if (sizeof($doc_list) == 0 || array_search($attachment->ID, $doc_list) !== FALSE) {
					$mime_type = substr($attachment->post_mime_type,0,5);
					if ( $doctype == 'all' ||
					    ($doctype == 'image' && $mime_type == 'image') ||
					    ($doctype == 'document' && $mime_type != 'image')) {

						$file_size = $this->get_file_size($attachment->ID);
						$fields_value = array(
							'id'			=> $attachment->ID,
							'title' 		=> htmlspecialchars(strip_tags($attachment->post_title)),
							'filename'		=> htmlspecialchars(basename(get_attached_file($attachment->ID))),
							'caption' 		=> htmlspecialchars(strip_tags($attachment->post_excerpt)),
							'description' 	=> htmlspecialchars(strip_tags($attachment->post_content)),
							'size' 			=> $this->get_file_size($attachment->ID),
							'icon'			=> $this->get_icon($attachment->ID, $attachment, $size),
							'date'			=> mysql2date($date_format, $attachment->post_date, TRUE),
							'type'			=> $this->get_type($attachment->post_mime_type)
						);
						$fields_value['label'] = ($label=="filename"?$fields_value['filename']:$fields_value['title']);

						if ($logged_users>0 && ! is_user_logged_in()) {
							$url =  ($this->options['login_url']==''?'#':$this->options['login_url']).
								'"  OnClick="alert(\''.addslashes(__('Attachments restricted to register users only', $this->textdomain)).'\');';
							$lock_icon = '<img class="lock" src="'.$this->url.'img/lock.png" height="16" width="16" alt="'.__('Document locked', $this->textdomain).'" />';
						}
						else {
							$lock_icon = '';

							switch ($this->options['link']) {
								case 'link': 	$url = get_permalink($attachment->ID); 			break;
								case 'file': 	$url = wp_get_attachment_url($attachment->ID); 	break;
								case 'direct':	$url = get_permalink($post->ID);				break;
							}
							if ($this->options['link'] != 'file') {
								$query = parse_url($url , PHP_URL_QUERY);
								$url = $url.($query['query']!=''?'&amp;':'?').htmlentities( 'aid='.$attachment->ID.'&sa='.$force_saveas );
							}
						}

						$full_link = '<a title="'.$fields_value['title'].
									'" href="'.$url.'"'.
									($nofollow?' rel="nofollow"':'').
									($target ?' target="_blank"':'').
									'>';

						switch ($size) {

							case 'custom':  //Functionality added by Jxs / www.jxs.nl
								$tmp = html_entity_decode (stripslashes($format));
								$tmp = preg_replace("/%URL%/",        ($hidepost_hide_link==1?'#':$url),$tmp);
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
								$tmp = preg_replace("/%DATE%/",		   strtoupper($fields_value['date']),$tmp);

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
											$caption_list[max(sizeof($caption_list)-1,0)] .= ' ('.$fields_value['size'].')';
										else {
											$caption_list[] = (($size!='small' || $display_label)?'<strong>'.__($EG_ATTACH_FIELDS_TITLE[$field], $this->textdomain).'</strong> : ':'').
											($first_field?$full_link:'').$fields_value[$field].($first_field?'</a>':'');
										}
										$first_field=FALSE;
									}
								} // End of foreach
								if ($icon) {
									$output .= '<dl class="attachments attachments-'.$size.'"><dt class="icon">'.
										($hidepost_hide_link==1?'':$full_link).$fields_value['icon'].
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
			remove_filter('icon_dirs', array(&$this, 'icon_dirs'));
			$output = trim($output);

			if ($output != '') {
				if ($size == 'custom' && $format_post != '')
					$output = html_entity_decode (stripslashes($format_pre)).$output.html_entity_decode (stripslashes($format_post));
				else {
					if (! $icon)
						$output = '<ul>'.$output.'</ul>';
				}
				if ( $title != '') {
					$output = ($titletag==''?'':'<'.$titletag.'>').
								htmlspecialchars(stripslashes(strip_tags($title))).
								($titletag==''?'':'</'.$titletag.'>').
								$output;
				}
				$output = '<div class="attachments">'.$output.'</div>';
				if (function_exists('hidepost_filter_post')) $output = hidepost_filter_post($output);

			} // End of $output
			return $output;
		} // End of get_attachments

		/**
		 * shortcode_is_visible
		 *
		 * Define is auto shortcode must be displayed of not.
		 *
		 * @return  int		1 if a shortcode is visble, 0 if not
		 */
		function shortcode_is_visible() {

			if (! is_array($this->options['shortcode_auto_where']))
				$list = array($this->options['shortcode_auto_where']);
			else
				$list = $this->options['shortcode_auto_where'];
/*
			if (sizeof($list)==4)
				$is_visible = TRUE;
			else {*/
				if (is_front_page() || is_home()) $current_page = 'home';
				elseif (is_singular()) $current_page = get_post_type();
				elseif (is_feed()) $current_page = 'feed';
				elseif (is_archive() || is_category() || is_tag() || is_date() || is_day() || is_month() || is_year()) $current_page = 'index';
				else $current_page='unknown';

				$is_visible = in_array($current_page, $list);
		/*	}*/
			return ($is_visible);
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

			$value = FALSE;
			if ($this->options['shortcode_auto_exclusive']>0) {
				if (isset($post)) {
					if (! isset($this->shortcode_exists[$post->ID])) {
						$this->shortcode_exists[$post->ID] = (strpos($post->post_excerpt.' '.$post->post_content, '['.EGA_SHORTCODE) !== FALSE);
					}
					$value = $this->shortcode_exists[$post->ID];
				}
			} // End of shortcode_auto_exclusive
			return ($value);
		} // End of shortcode_auto_check_manual_shortcode

		/**
		 * shortcode_auto_excerpt
		 *
		 * Display list of attachment in the post excerpt
		 *
		 * @return 	string				modified post content
		 */
		function shortcode_auto_excerpt($output) {

			if ($output &&
				$this->options['shortcode_auto'] == 3 &&
			     $this->shortcode_is_visible() &&
				! $this->shortcode_auto_check_manual_shortcode()) {

				$attrs = array( 'size' => $this->options['shortcode_auto_size'],
						'doctype'  	   => $this->options['shortcode_auto_doc_type'],
						'title'    	   => $this->options['shortcode_auto_title'],
						'titletag'     => $this->options['shortcode_auto_title_tag'],
						'label'    	   => $this->options['shortcode_auto_label'],
						'orderby'      => $this->options['shortcode_auto_orderby'].' '.$this->options['shortcode_auto_order'],
						'fields'	   => ($this->options['shortcode_auto_fields_def']?'':$this->options['shortcode_auto_fields']),
						'icon'         => $this->options['shortcode_auto_icon'],
						'limit'		   =>  $this->options['shortcode_auto_limit']
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

				$attrs = array( 'size' => $this->options['shortcode_auto_size'],
						'doctype'  	   => $this->options['shortcode_auto_doc_type'],
						'title'    	   => $this->options['shortcode_auto_title'],
						'titletag'     => $this->options['shortcode_auto_title_tag'],
						'label'    	   => $this->options['shortcode_auto_label'],
						'orderby'      => $this->options['shortcode_auto_orderby'].' '.$this->options['shortcode_auto_order'],
						'fields'	   => ($this->options['shortcode_auto_fields_def']?'':$this->options['shortcode_auto_fields']),
						'icon'         => $this->options['shortcode_auto_icon'],
						'limit'		   =>  $this->options['shortcode_auto_limit']
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

$eg_attach = new EG_Attachments('EG-Attachments', EGA_VERSION, EGA_COREFILE,
								EGA_TEXTDOMAIN, EGA_OPTIONS_ENTRY, $EG_ATTACH_DEFAULT_OPTIONS);
$eg_attach->set_stylesheets('css/eg-attachments.css');
$eg_attach->set_wp_versions('3.1',	'3.3.1', FALSE);

if (EGA_DEBUG_MODE)
	$eg_attach->set_debug_mode(TRUE, 'debug.log');

$eg_attach->load();

?>