<?php
/*
Package Name: EG-Forms
Package URI:
Description: Class for WordPress plugins
Version: 2.1.0
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

if (!class_exists('EG_Form_210')) {

	/**
	  * Class EG_Form_210
	  *
	  * Provide some functions to create a WordPress plugin
	  *
	 */
	Class EG_Form_210 {

		var $options_entry;
		var $options_group;
		var $textdomain;
		var $sidebar_callback;
		var $title;
		var $header;
		var $footer;

		var $tabs	  = array();
		var $sections = array();
		var $fields	  = array();

		var $debug_msg, $debug_file;

		function EG_Form_210($page_id, $title, $options_entry, $textdomain=FALSE, $header='', $footer='', $sidebar_callback=FALSE) {

			register_shutdown_function(array(&$this, '__destruct'));
			$this->__construct($page_id, $title, $options_entry, $textdomain, $header, $footer, $sidebar_callback);
		} // End of EG_Form_210

		/**
		  * Class contructor
		  * Define the plugin url and path. Declare action INIT and HEAD.
		  *
		  * @package EG-Forms
		  * @return object
		  */
		function __construct($page_id, $title, $options_entry, $textdomain=FALSE, $header='', $footer='', $sidebar_callback=FALSE) {

			$this->title 			 = $title;
			$this->header 			 = $header;
			$this->footer			 = $footer;
			$this->options_entry     = $options_entry;
			$this->options_group	 = $page_id.'_group';
			$this->textdomain		 = $textdomain;
			$this->sidebar_action    = 'eg_form_sidebar_'.$page_id;

			if ($sidebar_callback !== FALSE)
				add_action($this->sidebar_action, $sidebar_callback);

			add_action('admin_init',array( &$this, 'admin_init'));

		} // End of __construct

		/**
		 * Class destructor
		 *
		 * @package EG-Forms
		 *
		 * @return boolean true
		 */
		function __destruct() {
			// Nothing
		} // End of __destruct

		function add_tab($id, $title, $header='', $footer='') {
			$this->tabs[$id] = array('id' => $id, 'title' =>$title, 'header' => $header, 'footer' => $footer);
		} // End of add_tab

		function add_section($args) {
			$section_defaults = array(
					'tab'		=> '',
					'title' 	=> 'General',
					'header'	=> '',
					'footer'	=> ''
			);
			$this->sections[] = wp_parse_args($args, $section_defaults);

			return (sizeof($this->sections)-1);
		} // End of add_section

		function add_field($args) {
			$field_defaults = array(
					'section'	=> 0,
					'name'		=> '',
					'label'		=> '',
					'before'	=> '',
					'after'		=> '',
					'desc'		=> '',
					'type'		=> '',
					'options'	=> '',
					'size'		=> 'regular',
					'status'	=> ''
				);
			$this->fields[] = wp_parse_args($args, $field_defaults);

			return (sizeof($this->fields)-1);
		} // End of add_field

		function set_debug_mode($debug_mode = FALSE, $debug_file='') {
			$this->debug_msg = $debug_mode;
			if ($debug_file != '')
				$this->debug_file = $debug_file;
		}

		function admin_init() {
			wp_enqueue_style('dashboard');
			wp_enqueue_script( 'dashboard' );
			register_setting($this->options_group, $this->options_entry, array(&$this, 'options_validation'));
		} // End of admin_init

		function options_validation($inputs) {

			$this->display_debug_info('EG-Forms: Starting sanitizing options');

			$all_options = get_option($this->options_entry);

			$validated_inputs = array();
			foreach ($this->fields as $field) {

				// If field exist in plugin options
				$key = $field['name'];
				if ( isset($all_options[$key])) {
					switch ($field['type']) {
						case 'checkbox':
							if (! isset($inputs[$key])) $validated_inputs[$key] = 0;
							else $validated_inputs[$key] = $inputs[$key];
						break;

						case 'grid_select':
							$validated_inputs[$key] = array();
							if (isset($inputs[$key]) && is_array($inputs[$key])) {
								$i=1;
								foreach ($inputs[$key] as $input_value) {
									if ($input_value != '0') $validated_inputs[$key][$i++] = $input_value;
								}
							} // End of isset($inputs[$key]
						break;

						default:
							if (isset($inputs[$key])) {
								if (is_array($inputs[$key])) {
									$validated_inputs[$key] = (array)$inputs[$key];
								}
								else {
									if (is_float($inputs[$key])) $validated_inputs[$key] = floatval($inputs[$key]);
									elseif (is_int($inputs[$key])) $validated_inputs[$key] = intval($inputs[$key]);
									else $validated_inputs[$key] = trim(stripslashes($inputs[$key]));
								}
							} // End of isset($inputs[$key]
					} // End of switch
				} // End of field exists in plugin options
			} // End of foreach
			$this->display_debug_info('EG-Forms: Sanitize options done');

			return (wp_parse_args($validated_inputs, $all_options));
		} // End of options_validation

		function display_comment($field, $entry_name, $default_value) {
			echo '<p name="'.$field['name'].'">'.__($field['label'], $this->textdomain).'</p>';
		} // End of display_comment

		function display_radio($field, $entry_name, $default_value) {
			return ($this->display_checkbox($field, $entry_name, $default_value));
		}

		function display_checkbox($field, $entry_name, $default_value) {

			if (! is_array($field['options'])) {
				$string = '<fieldset>'.
					'<legend class="screen-reader-text">'.
					'<span>'.$field['label'].'</span>'.
					'</legend>'.
					'<label for="'.$field['name'].'">'.
					($field['before']==''?'':__($field['before'], $this->textdomain).' ').
					'<input type="'.$field['type'].'" id="'.$field['name'].'" name="'.$entry_name.'" value="1" '.checked(1, $default_value, FALSE).' '.$field['status'].' />'.
					($field['after']==''?'':' '.__($field['after'], $this->textdomain)).
					'</label>'.
					'</fieldset>';
			}
			else {
				$string = '<fieldset>'.
						'<legend class="screen-reader-text">'.
						'<span>'.$field['label'].'</span>'.
						'</legend><table class="eg-forms">';
				foreach ($field['options'] as $key => $value) {
					if ($field['type'] == 'radio') $input_name = $entry_name;
					else $input_name = $entry_name.'['.$key.']';
					if (!is_array($default_value)) $checked = ($key == $default_value?'checked':'');
					else $checked = (in_array($key, $default_value)===FALSE?'':'checked');

					$string .= '<tr><td valign="top">'.
						'<input type="'.$field['type'].'" name="'.$input_name.'" value="'.$key.'" '.$checked.' '.$field['status'].' /></td>'.
						'<td><label for="'.$key.'">'.__($value, $this->textdomain).'</label></td>'.
						'</tr>';
				}
				$string .= '</table></fieldset>';
			}
			return ($string);
		} // End of display_checkbox

		function display_hidden($field, $entry_name, $default_value) {
			$string = '<input type="'.$field['type'].'" name="'.$entry_name.'" id="'.$field['name'].'" value="'.$default_value.'" /> ';
			return ($string);
		} // End of display_hidden

		function display_password($field, $entry_name, $default_value) {
			return ($this->display_text($field, $entry_name, $default_value));
		} // End of display_password

		function display_text($field, $entry_name, $default_value) {
			$string = '<input type="'.$field['type'].'" class="'.$field['size'].'-text" name="'.$entry_name.'" id="'.$field['name'].'" value="'.htmlspecialchars($default_value).'" '.$field['status'].'/> ';
			return ($string);
		} // End of display_text

		function display_textarea($field, $entry_name, $default_value) {
			$string = '<textarea class="'.$field['size'].'-text" name="'.$entry_name.'" id="'.$field['name'].'" '.$field['status'].'>'.htmlspecialchars($default_value).'</textarea>';
			return ($string);
		} // End of display_textarea

		function display_select($field, $entry_name, $default_value) {
			$string = '<select name="'.$entry_name.'" id="'.$field['name'].'" >';
			foreach ($field['options'] as $key => $value) {
				$selected = ($default_value==$key?'selected':'');
				$string .= '<option value="'.$key.'" '.$selected.'>'.($value==''?'':__($value, $this->textdomain)).'</option>';
			}
			$string .= '</select>';
			return ($string);
		} // End of display_textarea

		function display_grid_select($field, $entry_name, $default_value) {
			if (! isset($field['options']['header']) || sizeof($field['options']['header']) == 0 ||
				! isset($field['options']['list'])   || sizeof($field['options']['list'])   == 0) {
				$string = '<p>'.__('No data available', $this->textdomain).'</p>';
			}
			else {
				$string = '<fieldset><legend class="screen-reader-text">'.__($field['label'], $this->textdomain).'</legend><table class="eg-forms"><thead><tr>';
				foreach ($field['options']['header'] as $item) {
					$string .= '<th>'.__($item, $this->textdomain).'</th>';
				}
				$string .= '</tr></thead><tbody>';
				foreach ($field['options']['list'] as $item) {
					$string .= '<tr><td>'.
						'<input type="text" value="'.$item['value'].'" disabled /></td><td>'.
						'<label for="'.$entry_name.'['.$item['value'].']">'.
						'<select name="'.$entry_name.'['.$item['value'].']" id="'.$field['name'].'['.$item['value'].']" >';
					foreach ($item['select'] as $key => $value) {
						if (sizeof($default_value)>0 &&
							isset($default_value[$item['value']]) &&
							$key == $default_value[$item['value']]) $selected = 'selected';
						else $selected = '';
						$string .= '<option value="'.$key.'" '.$selected.'>'.__($value, $this->textdomain).'</option>';
					}
					$string .=	'</select></label></td></tr>';
				}
				$string .= '</tbody></table></fieldset>';
			}
			return ($string);
		} // End of display_grid_select

		function display_field($field_id, $defaults) {
			$field = $this->fields[$field_id];

			$entry_name = $this->options_entry.'['.$field['name'].']';
			$default_value = $defaults[$field['name']];
			$single_chk = ( in_array($field['type'], array('checkbox', 'radio')) && !is_array($field['options']));

			$string= '<tr valign="top">'.
					'<th scope="row">'.
					($single_chk?__($field['label'], $this->textdomain):'<label for="'.$field['name'].'">'.__($field['label'], $this->textdomain).'</label>').
					'</th>'.
					'<td>'.
					($single_chk || $field['before']==''?'':__($field['before'], $this->textdomain)).
					call_user_func(array(&$this, 'display_'.$field['type']), $field, $entry_name, $default_value).
					($single_chk || $field['after']==''?'':__($field['after'], $this->textdomain)).
					($single_chk || $field['desc']==''?'':'<br />').
					($field['desc']==''?'':'<span class="description">'.__($field['desc'], $this->textdomain)).'</span>';
			$string.='</td></tr>';
			return ($string);
		} // End of display_field

		function display_section($section_id, $defaults) {

			$section = $this->sections[$section_id];
		?>
			<div class="postbox">
				<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle"><?php _e($section['title'], $this->textdomain); ?></h3>

				<div class="inside">
				<?php echo ($section['header']==''?'':'<p>'.__($section['header'], $this->textdomain).'</p>'); ?>
				<table class="form-table">
		<?php
				foreach ($this->fields as $field_id => $field) {
					if ($field['section'] == $section_id)
						echo $this->display_field($field_id, $defaults);
				}
		?>
				</table>
				<?php echo ($section['footer']==''?'':'<p>'.__($section['footer'], $this->textdomain).'</p>'); ?>
				<?php submit_button(); ?>
				<div class="clear"></div>
				</div>
			</div>
		<?php
		} // End of display_section


		function display_page($defaults) {
			$current_page = (isset($_REQUEST['page'])?$_REQUEST['page']:'');
			$current_tab  = (isset($_REQUEST['tab'])?$_REQUEST['tab']:'');

			$nav_links='';
			if (sizeof($this->tabs)>0) {

				foreach ($this->tabs as $id => $tab) {
					if ($current_tab=='') $current_tab = $id;
					$class = ( $current_tab == $id ) ? ' nav-tab-active' : '';
					$nav_links .= '<a class="nav-tab '.$class.'" href="?page='.$current_page.'&tab='.$id.'">'.__($tab['title'], $this->textdomain).'</a>';
				}
			}
			if ($nav_links != '')
				$nav_links = '<h2 class="nav-tab-wrapper">'.$nav_links.'</h2>';

			$display_sidebar = has_action($this->sidebar_action);
		?>
			<div class="wrap">
				<?php screen_icon(); ?>
				<h2><?php _e($this->title, $this->textdomain); ?></h2>
				<?php echo $nav_links; ?>
				<div id="poststuff" class="<?php echo (sizeof($this->tabs)>0?'eg-form-border':''); ?> metabox-holder <?php echo ($display_sidebar?'has-right-sidebar':''); ?>">
		<?php
			if ($display_sidebar) {
		?>
					<div id="side-info-column" class="inner-sidebar">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<?php do_action($this->sidebar_action); ?>
						</div>
					</div>
		<?php
			} // End of sidebar_callback
		?>
					<div id="post-body" <?php echo ($display_sidebar ?'class="has-sidebar"':'');?>>
						<div id="post-body-content" <?php echo ($display_sidebar ?'class="has-sidebar-content"':'');?>>
							<div id="normal-sortables" class="meta-box-sortables ui-sortable">

		<?php
			$this->display_form($defaults, $current_tab);
		?>
							</div>
						</div>
					</div>
					<br class="clear" />
				</div>
			</div>
		<?php
		} // End of display_page

		function display_form($defaults, $current_tab=NULL) {
			if ( sizeof($this->tabs)==0) $current_tab='';
			else {
				$first_tab    = reset($this->tabs);
				$current_tab  = (isset($current_tab)?$current_tab:(isset($_REQUEST['tab'])?$_REQUEST['tab']:$first_tab['id']));
			}
		?>
							<form method="post"action="<?php echo admin_url('options.php'); ?>" >
								<?php settings_fields($this->options_group); ?>
								<?php echo ($this->header==''?'':'<p>'.__($this->header, $this->textdomain).'</p>'); ?>
					<?php
								if (isset($this->tabs[$current_tab]) && $this->tabs[$current_tab]['header']!='')
									echo '<p>'.__($this->tabs[$current_tab]['header'], $this->textdomain).'</p>';

								foreach ($this->sections as $section_id => $section) {
									if (sizeof($this->tabs)==0 || $section['tab'] == $current_tab) {
										$this->display_section($section_id, $defaults);
									}
								} // End of foreach section
								if (isset($this->tabs[$current_tab]) && $this->tabs[$current_tab]['footer']!='')
									echo '<p>'.__($this->tabs[$current_tab]['footer'], $this->textdomain).'</p>';

								echo ($this->footer==''?'':'<p>'.__($this->footer, $this->textdomain).'</p>');
					?>
							</form>
		<?php
		} // End of display_form

		/**
		 * display_debug_info
		 *
		 * @package EG-Plugin
		 *
		 * @param	string	$msg	message to display
		 * @return 	none
		 */
		function display_debug_info($msg) {

			if ($this->debug_msg) {
				$debug_info = debug_backtrace(FALSE);
				$output = date('d-M-Y H:i:s').' - '.$debug_info[1]['function'].' - '.$debug_info[2]['function'].' - '.$msg;

				if ($this->debug_file != '')
					file_put_contents($this->debug_file, $output."\n", FILE_APPEND);
				else
					echo $output.'<br />';
			}
		} // End of display_debug_info

	} // End of class
} // End of class_exists

?>