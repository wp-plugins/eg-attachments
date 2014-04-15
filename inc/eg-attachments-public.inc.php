<?php

if (! class_exists('EG_Attachments_Public')) {

	/**
	 * Class EG_Attachments_Public
	 *
	 * Implement a shortcode to display the list of attachments in a post.
	 *
	 * @package EG-Attachments
	 */
	Class EG_Attachments_Public extends EG_Plugin_133 {

		var $order_by 	= 'title';
		var $order 		= 'ASC';

		/**
		  *  init
		  *
		  * Declare shortcode, and auto-shortcode
		  *
		  * @package EG-Attachments
		  *
		  * @param  none
		  * @return	none
		  */
		function init() {

			add_action('template_redirect', array(&$this, 'manage_link'));

			// Add the shortcode
			add_shortcode(EGA_SHORTCODE, array(&$this, 'get_attachments'));

			// Add the auto shortcode
			if ( $this->options['shortcode_auto'] > 0 ) {
				add_filter('the_content',     array(&$this, 'shortcode_auto_content'));
				if ($this->options['shortcode_auto'] == 3) {
					add_filter('get_the_excerpt', array(&$this, 'shortcode_auto_excerpt'));
				}
			}
		} // End of init

		/**
		  *  manage_link
		  *
		  * Manage the file download
		  *
		  * @package EG-Attachments
		  *
		  * @param  none
		  * @return	none
		  */
		function manage_link() {
			global $post;

			// Ensure that the link is coming from EG-Attachment
			if ( isset($_GET['aid']) /*&& is_numeric($_GET['aid']) */) {

				// First security check. If post not defined, potential hack tentative.
				if (! isset($post)) {
					wp_die(__('Something is going wrong. Bad address, or perhaps you try to access to a private document.', $this->textdomain));
				}

				// Are we in an attachment? or a post?
				if ( is_attachment() ) {

					$attach_id    = $post->ID;
					$attach_title = $post->post_title;
					$parent_id    = ( isset($_GET['pid']) ? $_GET['pid'] : reset( get_post_ancestors($attach_id) ) );
					$parent_title = get_post_field('post_title', $parent_id);
				}
				else {

					$parent_id    = $post->ID;
					$parent_title = $post->post_title;
					$attach       = get_post($_GET['aid']);
					if (isset($attach) && $attach && 'attachment' == $attach->post_type) {
						$attach_id    = $attach->ID;
						$attach_title = get_post_field('post_title',$attach_id) ;
					}
				}

				if ( isset($attach_id) ) {

					$this->record_click($parent_id, $parent_title, $attach_id, $attach_title);

					// $parent_id = reset(get_post_ancestors($attach_id));

					// Second security check: private posts / pages
					if ('private' == get_post_field('post_status', $parent_id) && !is_user_logged_in()) {
						wp_die(__('This post is private. You must be a user of the site, and logged in, to display this file.', $this->textdomain));
					}

					// Third security check: protected post
					if (post_password_required($parent_id)) {
						wp_die(__('This post is password protected. Please go to the site, and enter the password required to display the document', $this->textdomain));
					}

					if ($_GET['sa'] < 1) {
						if ( !is_attachment() ) {
							wp_redirect(esc_url(wp_get_attachment_url($attach_id)));
							exit;
						}
					}
					else { // Force "Save as"
						$chunksize = 2*(1024*1024);

						$file_path = get_attached_file($attach_id);
						$stat = @stat($file_path);
						$etag = sprintf('%x-%x-%x', $stat['ino'], $stat['size'], $stat['mtime'] * 1000000);
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
						header('Last-Modified: ' . date('r', $stat['mtime']));
						header('Etag: "' . $etag . '"');
						header('Content-Length: '.$stat['size']);
						header('Accept-Ranges: bytes');
						ob_flush();
						flush();
						if ($stat['size'] < $chunksize) {
							@readfile($file_path);
						}
						else {
							$handle = fopen($file_path, 'rb');
							while (!feof($handle)) {
								echo fread($handle, $chunksize);
								ob_flush();
								flush();
							}
							fclose($handle);
						}
						exit();
					} // End of force save as

				} // End of isset attach_id
			} // End of if $_GET[aid]

		} // End of manage_link

		/**
		  *  record_click
		  *
		  * Record the click (download) in the statistics table
		  *
		  * @package EG-Attachments
		  *
		  * @param  int		$parent_id		the post from where the user click
		  * @param	string	$parent_title	Title of this post
		  * @param	int		$attach_id		id of the attachment to download
		  * @param	strong	$attach_title	Title of the attachment
		  * @return	none
		  */
		function record_click($parent_id, $parent_title, $attach_id, $attach_title) {
			global $wpdb;

			$stats_enable = $this->options['stats_enable'] && $this->options['clicks_table'];
			if ($stats_enable && $this->options['stats_ip_exclude'] != '') {
				$stat_ip = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : FALSE);
				if ($stat_ip !== FALSE ) {
					$stats_enable = (! in_array($stat_ip, explode(',', $this->options['stats_ip_exclude'])) );
				}
			}

			if ($stats_enable) {

				// Count click
				$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'eg_attachments_clicks '.
									'(click_date,post_id,post_title,attach_id,attach_title,clicks_number) values '.
									'(CURRENT_DATE(),%d,%s,%d, %s, %d)'.
									'ON DUPLICATE KEY UPDATE clicks_number=clicks_number+1',
									array($parent_id, $parent_title, $attach_id, $attach_title, 1));
				$wpdb->query($sql);
			} // End of stat enable
		} // End of record_click

		/**
		  *  get_file_size() - Try to get the size of the specified file
		  *
		  * @package EG-Attachments
		  *
		  * @param  int     $attachment_id		id of attachment to get size
		  * @return	float 						size of the attachment
		  */
		function get_file_size( $attachment) {

			$docsize = $attachment->file_size;
			if ( FALSE !== $docsize && 0 != $docsize && '' != $docsize ) {
				$size_value = explode(' ',size_format($docsize, 0)); // WP function found in file wp-includes/functions.php
				$docsize = $size_value[0].' '.__($size_value[1]);
			}

			if ( FALSE === $docsize || 0 == $docsize | '' == $docsize )
				return __('unknown', $this->textdomain);
			else
				return ($docsize);

		} /* End of get_file_size */

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
			if (!is_array($args)) {
				$new_args = $args ;
			}
			else {
				// Add the icons path of the current plugin
				//$new_args = array_merge(array($this->path.'img/flags' => $this->url.'img/flags'),$args);
				if ($this->options['icon_path'] !='' &&
					$this->options['icon_url']  !='' &&
					file_exists(str_replace('\\','/',trailingslashit(ABSPATH).$this->options['icon_path']))) {

					$new_args = array_merge(array($this->path.'img/'.$this->options['icon_set'] => $this->url.'img/'.$this->options['icon_set']),
							array(str_replace('\\','/',trailingslashit(ABSPATH).$this->options['icon_path']) => trailingslashit(get_bloginfo('home')).$this->options['icon_url']),	$args);
				}
				else {
					$new_args = array_merge(array($this->path.'img/'.$this->options['icon_set'] => $this->url.'img/'.$this->options['icon_set']),$args);
				}
			}
			return ($new_args);
		} // End of icon_dirs


		function get_icon_url($id, $doctype, $icon_image) {

			$icon_url = false;
			// --- New in 2.0.1
			if ( 'thumbnail' == $icon_image && $image = image_downsize($id, 'thumbnail'))
				$icon_url = $image[0];
			else
				$icon_url = wp_mime_type_icon($id);

			if ( ! $icon_url ) {
				if (function_exists('includes_url'))
					$icon_url = includes_url('/images/crystal/default.png');
				else
					$icon_url = trailingslashit(get_bloginfo('wpurl')).WPINC.'/images/crystal/default.png';
			}
			return ($icon_url);
		} // End of get_icon_url

		/**
		  * Get_icon() - Get the thumbnail of the atttachment
		  *
		  * @package EG-Attachments
		  *
		  * @return string html entities IMG
		  */
		function get_icon($html, $attachment, $doctype, $icon_image) {
			$output = $html;

			preg_match_all("/%ICON-([0-9]+)x([0-9]+)%/", $html, $matches);
			if ($matches) {
				$icon_url = $this->get_icon_url($attachment->ID, $doctype, $icon_image);
				if ( '' != $attachment->post_content )
					$description = esc_html($attachment->post_content);
				elseif ( '' != $attachment->post_title )
					$description = esc_html($attachment->post_title);
				else
					$description = esc_html($attachment->post_name);

				foreach ($matches[0] as $key => $pattern) {
					$width  = $matches[1][$key].'px';
					$height = $matches[2][$key].'px';

// style=\"width='.$width.'!important; height='.$height.'!important; max-width=none!important;\"

					$output = preg_replace('/'.$pattern.'/', '<img src="'.$icon_url.'" width="'.$width.'" height="'.$height.'" alt="'.$description.'" />', $output);

				} // End of foreach
			} // End of if matches
			return ($output);
		} /* end of get_icon */

		function where_post_mime_type($args) {

			if ($args != '') {
				global $wpdb;
				return (str_replace($wpdb->prefix.'posts.post_mime_type LIKE \'notimage/%\'', $wpdb->prefix.'posts.post_mime_type NOT LIKE \'image/%\'',$args));
			}
		} // End of where_post_mime_type


		function sort_list($a, $b) {
			return ( 'ASC' === $this->order
						?  $a->{$this->order_by} > $b->{$this->order_by}
						:  $a->{$this->order_by} < $b->{$this->order_by}
					);
		} // End of function sort_list

		/**
		  * The eg-attachments shortcode.
		  *
		  * This implements the functionality of the Attachments Shortcode for displaying
		  * WordPress documents on a post.
		  *
		  * @package EG-Attachments
		  *
		  * @param array $attr Attributes of the shortcode.
		  * @return string HTML content to display gallery.
		  */
		function get_attachments($atts) {
			global $wpdb;

			global $EGA_SHORTCODE_DEFAULTS;
			global $EGA_FIELDS_ORDER_KEY;
			global $post;

			/**
			  * Extracting parameters
			  *
			  */
			 // TODO: replace with get_shortcode_defaults
			$EGA_SHORTCODE_DEFAULTS['force_saveas'] 		= $this->options['force_saveas'];
			$EGA_SHORTCODE_DEFAULTS['logged_users'] 		= $this->options['logged_users_only'];
			$EGA_SHORTCODE_DEFAULTS['login_url'] 			= $this->options['login_url'];
			$EGA_SHORTCODE_DEFAULTS['nofollow'] 			= $this->options['nofollow'];
			$EGA_SHORTCODE_DEFAULTS['icon_image'] 			= $this->options['icon_image'];
			$EGA_SHORTCODE_DEFAULTS['target'] 				= $this->options['target_blank'];
			$EGA_SHORTCODE_DEFAULTS['exclude_thumbnail']	= $this->options['exclude_thumbnail'];

			extract( shortcode_atts( $EGA_SHORTCODE_DEFAULTS, $atts ) );

			/* Check Security: if the post is password protected, or private, we don't display anything */
			if ( post_password_required($post->ID) ||
				( 'private' == get_post_field('post_status', $post->ID) && !is_user_logged_in() ) ||
				( 2 == $logged_users && !is_user_logged_in() ) ) {
				return '';
			}

			if (0 == $id)
				$id = $post->ID;

			/**
			  * Managing compatibility
			  *
			  */
			if (0 != $docid && '' == $include)
				$include = $docid;

			/**
			  * Managing parameters
			  *
			  */
			list($this->order_by, $this->order) = explode(' ', strtolower($orderby));
			list($orderby_default, $order_default) = $EGA_SHORTCODE_DEFAULTS['orderby'];

			$this->order_by = (isset($EGA_FIELDS_ORDER_KEY[$this->order_by]) ? $EGA_FIELDS_ORDER_KEY[$this->order_by] : $orderby_default);
			$this->order    = strtoupper(in_array($this->order, array('asc', 'desc')) ? $this->order : $order_default);

			if ('' == $template) {
				if ( 'custom' == $size ) {
					if ('' != $this->options['legacy_custom_format'])
						$template = $this->options['legacy_custom_format'];
				}
				elseif ($size != '') {
					$template = $size;
					if (FALSE === strpos($size, '-list') && ! $icon) $template .= '-list';
				}
			} // No template defined

			/**
			  * Getting the template
			  *
			  */
			$error_msg = '';
			$cache_entry = strtolower($this->name).'-shortcode-tmpl';
			$templates = (EGA_ENABLE_CACHE ? get_transient($cache_entry) : FALSE);
			if (FALSE !== $templates && isset($templates[$template])) {
				$template_content = $templates[$template];
			}
			else {
				// Query
				$tmpl = get_posts( array('post_type' => EGA_TEMPLATE_POST_TYPE, 'name' => $template));
				if (! $tmpl) {
					$error_msg = esc_html__('Template doesn\'t exists. Use default', $this->textdomain);
					$tmpl = get_posts( array('post_type' => EGA_TEMPLATE_POST_TYPE, 'name' => $EGA_SHORTCODE_DEFAULTS['shortcode_auto_size']));
				}

				// Parse the result
				if ($tmpl) {
					if (FALSE === $templates) $templates = array();

					$template_content = EG_Attachments_Common::parse_template($tmpl[0]->post_content);
					if (FALSE === $template) {
						$error_msg = esc_html__('Error during processing shortcode template', $this->textdomain);
					}
					elseif (EGA_ENABLE_CACHE) {
						$templates[$template] = $template_content;
						set_transient($cache_entry, $templates, EGA_TEMPLATE_CACHE_EXPIRATION);
					}
				} // End of template found
			}

			/**
			  * Preparing query
			  *
			  */
			$params = array('numberposts' 		=> $limit,
							'post_type'   		=> 'attachment',
							'suppress_filters' 	=> false,
							'orderby'			=> $this->order_by,
							'order'				=> $this->order);
			if ( 0 < $id ) {
				$params['post_parent'] = $id;
				if ( 0 !== intval($exclude_thumbnail) ) {
					$featured_id = get_post_thumbnail_id($id);
					if ( FALSE !== $featured_id && '' != $featured_id )
						$exclude = ( '' == $exclude ? $featured_id : $exclude.','.$featured_id );
				} // End of exclude thumbnail
			} // End of parent specified

			if ( '' != $include )
				$params['include'] = $include;

			if ( '' != $exclude )
				$params['exclude'] = $exclude;

			if ( 'image' == $doctype )
				$params['post_mime_type'] = 'image';
			elseif ('' != $doctype && 'all' != $doctype)
				$params['post_mime_type'] = 'notimage';

			if ('' != $tags) {
				$list = explode(',', $tags);
				if (! is_array($list)) $params['tag'] = $list;
				else {
					if (sizeof($list) == 1) $params['tag'] = current($list);
					else $params['tag_slug__in'] = $list;
				}
			} // End of tags != ''
			else {
				if ('' != $tags_and) {
					$list = explode(',', $tags_and);
					if (! is_array($list)) $params['tag'] = $list;
					else {
						if (sizeof($list) == 1) $params['tag'] = current($list);
						else $params['tag_slug__and'] = $list;
					}
				} // End of tags_and=''
			} // End of tags=''

			/**
			  * Is there a similar query in the cache?
			  *
			  */
			$cache_entry = strtolower($this->name).'-cache-'.$id;
			$cache_id    = md5(implode('', $params));
			$cache 		 = (EGA_ENABLE_CACHE ? get_transient($cache_entry) : FALSE);
			if (FALSE !== $cache && isset($cache[$cache_id])) {
				$attachments = $cache[$cache_id];
			}
			else {
				/**
				  * Query DB
				  */
				add_filter('posts_where', array(&$this, 'where_post_mime_type') );
				$attachments = get_posts($params);
				remove_filter('posts_where',array(&$this, 'where_post_mime_type') );

				if ( $attachments && sizeof($attachments) > 0 ) {

					/* --- Get file date and size --- */
					foreach ($attachments as $attachment) {
						$info = @stat(get_attached_file($attachment->ID));
						if ( FALSE !== $info ) {
							$attachment->file_date = date('Y-m-d H:i:s', $info['mtime']);
							$attachment->file_size = intval($info['size']);
						} // End of get info
					} // End of foreach attachment

					/* --- Sort list of --- */
					/* Get posts allows only the following sort keys: 'name', 'author', 'date', 'title', 'modified', 'menu_order', 'ID', 'rand', 'comment_count' */
					if ( ! in_array( $this->order_by, array( 'name', 'author', 'date', 'title', 'modified', 'menu_order', 'ID', 'rand', 'comment_count' ) ) ) {
						uasort($attachments, array($this, 'sort_list') );
					}

					if ( EGA_ENABLE_CACHE ) {
						$cache[$cache_id] = $attachments;
						set_transient($cache_entry, $cache, EGA_SHORTCODE_CACHE_EXPIRATION);
					}

				} // End of list of attachments not found
			}

			/**
			  * Building output
			  *
			  */
			if (!$attachments || sizeof($attachments) == 0 ) {
				return '';
			}

			/* --- Prepare loop --- */
			$date_format = ( $this->options['date_format']!='' ? $this->options['date_format'] : get_option('date_format') );
			$output = '';
			add_filter('icon_dirs', array(&$this, 'icon_dirs'));

			/* ---  if required, getting statistics --- */
			$click_counts = array();
			if (FALSE !== strpos($template_content['loop'], '%COUNTER%') && $this->options['stats_enable'] && $this->options['clicks_table']) {

				$cache_entry = strtolower($this->name).'-cache-click-'.$id;
				$click_counts = (EGA_ENABLE_CACHE ? get_transient($cache_entry) : FALSE);

				if ( FALSE === $click_counts ) {

					$sql = $wpdb->prepare('SELECT attach_id, SUM(clicks_number) AS clicks_number'.
							' FROM '.$wpdb->prefix.'eg_attachments_clicks '.
							' WHERE post_id=%d'.
							' GROUP BY attach_id', $post->ID);

					$results = $wpdb->get_results($sql);
					foreach ($results as $value) {
						$click_counts[intval($value->attach_id)] = intval($value->clicks_number);
					}

					if ( EGA_ENABLE_CACHE ) {
						set_transient($cache_entry, $click_counts, 60 );
					}
				} // End of nothing in the cache
			} // End of get click counters

			/* --- Starting loop --- */
			reset($attachments);
			foreach ($attachments as $attachment) {

				if ( post_password_required($attachment->post_parent) || ('private' == get_post_status($attachment->ID)  && !is_user_logged_in()) ) {
					continue;
				}

				$alt_img_icon 	= '';
				$lock_icon 		= '';
				$url 			= '';
				$click_count 	= '';

				if ( 1 == $logged_users && !is_user_logged_in()) {
					$url = $file_url = $attach_url = $direct_url = ( '' != $this->options['login_url'] ?
							$this->options['logged_users'] :
							wp_login_url( apply_filters( 'the_permalink', get_permalink( $post->ID )))
					);
					$alt_img_icon = __('You need to login to access to the attachments', $this->textdomain);
				} // End of attachments requied login

				if ($alt_img_icon != '') {
					$lock_icon = '<img class="lock" src="'.$this->url.'img/lock.png" height="16" width="16" alt="'.$alt_img_icon.'" />';
				}

				if ('' == $url) {
					$query_args = array('aid' => $attachment->ID, 'sa' => $force_saveas);
					$attach_url = add_query_arg( array_merge(array('pid' => $post->ID),$query_args), get_permalink($attachment->ID) );

					$file_url   = wp_get_attachment_url( $attachment->ID );
					$direct_url = add_query_arg( $query_args, get_permalink($post->ID) );

					if ('link' == $this->options['link'])
						$url = $attach_url;
					elseif ('file' == $this->options['link'])
						$url = $file_url;
					else
						$url = $direct_url;
				} // Url empty

				$file_date = mysql2date($date_format, $attachment->file_date, TRUE);
				$post_date = mysql2date($date_format, $attachment->post_date, TRUE);

				$counter = (isset($click_counts[$attachment->ID]) ? $click_counts[$attachment->ID] : 0);

				$item = html_entity_decode(stripslashes($template_content['loop']));
				$item = preg_replace("/%LINK_URL%/",		$attach_url,													$item);
				$item = preg_replace("/%URL%/",				$url,															$item);
				$item = preg_replace("/%FILE_URL%/",		$file_url,														$item);
				$item = preg_replace("/%DIRECT_URL%/",		$direct_url,													$item);
				$item = preg_replace("/%GUID%/",			$attachment->guid,												$item);
				$item = $this->get_icon($item, $attachment, $doctype, $icon_image);
				$item = preg_replace("/%ICONURL%/",			$this->get_icon_url($attachment->ID, $doctype, $icon_image),	$item);
				$item = preg_replace("/%TITLE%/",			esc_html($attachment->post_title),								$item);
				$item = preg_replace("/%TITLE_LABEL%/",		esc_html__('Title'), 											$item);
				$item = preg_replace("/%CAPTION%/",    		esc_html($attachment->post_excerpt),							$item);
				$item = preg_replace("/%CAPTION_LABEL%/", 	esc_html__('Caption', $this->textdomain), 						$item);
				$item = preg_replace("/%DESCRIPTION%/", 	esc_html($attachment->post_content),							$item);
				$item = preg_replace("/%DESCRIPTION_LABEL%/", esc_html__('Description', $this->textdomain),			 		$item);
				$item = preg_replace("/%FILENAME%/",		esc_html(basename(get_attached_file($attachment->ID))),			$item);
				$item = preg_replace("/%FILENAME_LABEL%/",	esc_html__('Filename', $this->textdomain), 						$item);
				$item = preg_replace("/%FILESIZE%/",		esc_html($this->get_file_size($attachment /* ->ID)*/)),			$item);
				$item = preg_replace("/%FILESIZE_LABEL%/",	esc_html__('Size', $this->textdomain), 							$item);
				$item = preg_replace("/%ATTID%/",       	$attachment->ID,												$item); //For use with stylesheets
				$item = preg_replace("/%TYPE%/",		  	esc_html(strtoupper($this->get_type($attachment->post_mime_type))),	$item);
				$item = preg_replace("/%TYPE_LABEL%/",	 	esc_html__('Type', $this->textdomain), 							$item);
				$item = preg_replace("/%DATE%/",		   	esc_html($file_date),											$item);
				$item = preg_replace("/%POSTDATE%/",		esc_html($post_date),											$item);
				$item = preg_replace("/%DATE_LABEL%/",  	esc_html__('Date', $this->textdomain), 							$item);
				$item = preg_replace("/%SHOWLOCK%/",  		$lock_icon, 													$item);
				$item = preg_replace("/%COUNTER%/",  		esc_html( $counter ), 											$item);
				//if ('' === $click_count || 0 == $click_count)
				//	$item = preg_replace("/%COUNTER_LABEL%/",	'', 														$item);
				//else
					$item = preg_replace("/%COUNTER_LABEL%/",	esc_html__((2>$counter?'click':'clicks'), $this->textdomain),		$item);

				if ( $nofollow )
					$item = preg_replace("/%NOFOLLOW%/",	'rel="nofollow"', 										$item);
				else
					$item = preg_replace("/%NOFOLLOW%/",	'', 													$item);

				if ( $target ) {
					$item = preg_replace("/%TARGET=([^%]*)%/",	'target=$1', 									$item);
					$item = preg_replace("/%TARGET%/",		'target="_blank"', 										$item);
				}
				else {
					$item = preg_replace("/%TARGET=([^%]*)%/",	'', 												$item);
					$item = preg_replace("/%TARGET%/",		'', 													$item);
				}
				$output .= $item;

			} // End foreach attachment
			remove_filter('icon_dirs', array(&$this, 'icon_dirs'));

			if ($output != '') {
				//if ( $this->options['stats_enable'] > 0 ) {
				//	$output = $this->add_click_counter($output);
				//}
				$output = html_entity_decode($template_content['before']) . $output . html_entity_decode($template_content['after']);
				$output = $this->shortcode_title($output, $title, $titletag);
				$output = '<div class="attachments">'.$output.'<p>'.$error_msg.'</p></div>';
			} // End of $output

/* --- [2.0.1] Remove this cache
			if ( FALSE === $cache || !isset($cache[$cache_id]) ) {
				if ( FALSE === $cache )
					$cache = array();

				$cache[$cache_id] = $output;
				set_transient($this->name.'-lists', $cache, EGA_SHORTCODE_CACHE_EXPIRATION);
			} // End of cache empty
*/
			return ($output);
		} // End of get_attachments

		function get_shortcode_parameters() {
			return array(
						'template' 	=> $this->options['shortcode_auto_template'],
						'doctype'	=> $this->options['shortcode_auto_doc_type'],
						'title'		=> $this->options['shortcode_auto_title'],
						'titletag'  => $this->options['shortcode_auto_title_tag'],
						'orderby'   => $this->options['shortcode_auto_orderby'].' '.$this->options['shortcode_auto_order'],
						'limit'		=> $this->options['shortcode_auto_limit']
					);

		} // End of get_shortcode_parameters

		function shortcode_auto_excerpt($output) {

			if ($output &&
				3 == $this->options['shortcode_auto'] &&
			    $this->shortcode_is_visible() &&
				$this->shortcode_auto_check_manual_shortcode(EGA_SHORTCODE)) {

				$attrs = $this->get_shortcode_parameters();

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


			if ($this->options['shortcode_auto']  > 0 	&&
			 	$this->shortcode_is_visible() 			&&
				$this->shortcode_auto_check_manual_shortcode(EGA_SHORTCODE)) {

				$attrs = $this->get_shortcode_parameters();

				$shortcode_output = $this->get_attachments($attrs);

				switch ($this->options['shortcode_auto']) {
					case 2: // At the end of post
						if (FALSE === strpos( $content, '#more-'.$post->ID) && FALSE === strpos($content, 'class="more-link"') )
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
								$parts = preg_split('/(<span id="more-' . $post->ID . '"><\/span>)/', $content, -1,  PREG_SPLIT_DELIM_CAPTURE);
								$content = $parts[0].$parts[1].$shortcode_output.$parts[2];
							} // End of detect tag "more"
						} // End of teaser case
					break;
				} // End of switch
			} // End of shortcode is activated and visible
			return ($content);
		} // End of shortcode_auto_content

		/**
		 * load
		 *
		 * Add "init" hook to the plugin
		 *
		 * @param 	none
		 * @return 	none
		 */
		function load() {
			parent::load();
			add_action('init', array( &$this, 'init'));
		} // End of load

	} /* End of Class */

} /* End of if class_exists */

$eg_attach_public = new EG_Attachments_Public(
							'EG-Attachments',
							EGA_VERSION,
							EGA_OPTIONS_ENTRY,
							EGA_TEXTDOMAIN,
							EGA_COREFILE,
							$EGA_DEFAULT_OPTIONS);

$eg_attach_public->add_stylesheet('css/eg-attachments.css');
$eg_attach_public->load();

?>