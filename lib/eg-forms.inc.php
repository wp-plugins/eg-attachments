<?php
/*
Plugin Name: EG-Forms
Plugin URI:
Description: Class to build admin forms
Version: 1.0.0
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

if (!class_exists('EG_Forms')) {

	Class EG_Forms {

		var $sections = array();
		var $fields   = array();
		var $buttons  = array();

		/**
		 * EG_Forms (constructor)
		 *
		 * Init object
		 *
		 * @package EG-Forms
		 *
		 * @param 	string	$title			form title
		 * @param 	string	$header		text to display before the first section or field
		 * @param	string	$footer		text to display at the form's bottom (before submit button)
		 * @param	string	$textdomain	textdomain
		 * @param	string	$url			url for form action
		 * @param	string	$id_icon		icon id to display before the title
		 * @param	string	$security_key	key to generate nonce
		 * @param	string	$author_address	author email or URL (must include mailto: or http:
		 * @return 	none
		 */
		function EG_Forms($title, $header, $footer, $textdomain, $url, $id_icon, $security_key, $author_address) {
			register_shutdown_function(array(&$this, "__destruct"));
			$this->__construct($title, $header, $footer, $textdomain, $url, $id_icon, $security_key, $author_address);
		}

		/**
		 * __construct(constructor)
		 *
		 * Init object
		 *
		 * @package EG-Forms
		 *
		 * @param 	string	$title			form title
		 * @param 	string	$header		text to display before the first section or field
		 * @param	string	$footer		text to display at the form's bottom (before submit button)
		 * @param	string	$textdomain	textdomain
		 * @param	string	$url			url for form action
		 * @param	string	$id_icon		icon id to display before the title
		 * @param	string	$security_key	key to generate nonce
		 * @param	string	$author_address	author email or URL (must include mailto: or http:
		 * @return 	none
		 */
		function __construct($title, $header, $footer, $textdomain, $url, $id_icon, $security_key, $author_address) {
			$this->title          = $title;
			$this->header         = $header;
			$this->footer         = $footer;
			$this->textdomain     = $textdomain;
			$this->url    		  = $url; // sanitize_url($url);
			$this->id_icon    	  = $id_icon;
			$this->security_key   = $security_key;
			$this->author_address = $author_address;
		}

		/**
		 * __destruct(constructor)
		 *
		 * @package EG-Forms
		 *
		 * @param	none
		 * @return 	none
		 */
		function __destruct() {
		}

		/**
		 * save_form
		 *
		 * @package EG-Forms
		 *
		 * @param	string	$file_path	configuration file
		 * @return 	none
		 */
		function save_form($file_path) {
			$handle = fopen($file_path, 'w');
			fwrite($handle, serialize($this));
			fclose($handle);
		}

		/**
		 * read_form
		 *
		 * @package EG-Forms
		 *
		 * @param	string	$file_path	configuration file
		 * @return 	object				object of EG_Forms class
		 */
		function read_form($file_path) {
			return unserialize(file_get_contents($file_path));
		}

		/**
		 * add_section
		 *
		 * Init object
		 *
		 * @package EG-Forms
		 *
		 * @param 	string	$section_title	title of a section (can be '')
		 * @param 	string	$header		text to display before the first field of the section
		 * @param	string	$footer		text to display after the last field of the section
		 * @return 	string				id of the section
		 */
		function add_section($section_title, $header='', $footer='') {
			$index = 'eg_form_s'.sizeof($this->sections);
			$this->sections[$index]->title  = $section_title;
			$this->sections[$index]->header = $header;
			$this->sections[$index]->footer = $footer;
			$this->sections[$index]->groups = array();
			return ($index);
		}

		/**
		 * add_group
		 *
		 * Init object
		 *
		 * @package EG-Forms
		 *
		 * @param	string	$section_id	id of the section within we have to add the group
		 * @param 	string	$group_title	title of the group
		 * @param 	string	$header		text to display before the first field of the group
		 * @param	string	$footer		text to display after the last field of the group
		 * @return 	string				id of the group
		 */
		function add_group($section_id, $group_title, $header='', $footer='') {

			$index = FALSE;
			if (isset($this->sections[$section_id])) {
				$groups = & $this->sections[$section_id]->groups;
				$index = $section_id.'_g'.sizeof($groups);
				$groups[$index]->title  = $group_title;
				$groups[$index]->header = $header;
				$groups[$index]->footer = $footer;
				$groups[$index]->fields = array();
			}
			return ($index);
		}

		/**
		 * set_field_values
		 *
		 * for select and input radio only
		 * define list of values to use with these HTML tag
		 *
		 * @package EG-Forms
		 *
		 * @param	string	$option_name	id of the field to modify
		 * @param 	array		$values		list of value to use
		 * @return 	none
		 */
		function set_field_values($option_name, $values) {
			if (is_array($values)) {
				foreach ($values as $key => $value) {
					$values[$key] = $value;
				}
			}
			$this->fields[$option_name]->values = $values;
		}

		/**
		 * add_field
		 *
		 * Add a field the form
		 *
		 * @package EG-Forms
		 *
		 * @param	string	$section_id	id of the section within we have to add the field
		 * @param 	string	$group_id		id of the group within we have to add the field
		 * @param 	string	$type		text, select, checkbox, radio
		 * @param	string	$label		label of the field
		 * @param	string	$text_before	text to display before the field
		 * @param	string	$text_after	text to display after the field
		 * @param	string	$description	description of the field
		 * @param	string	$option_name	id of the field (must be same name than option entry we want to modify)
		 * @param	string	$status		disabled for example
		 * @param	string	$size			small, regular or large
		 * @param	array		$values		list of values to use (for select and radio only)
		 * @return 	string				id of the field
		 */
		function add_field($section_id,
							$group_id,
							$type,
							$label,
							$option_name,
							$text_before = '',
							$text_after  = '',
							$description = '',
							$status      = '',
							$size        = 'regular',
							$values      = FALSE) {
			$index = FALSE;
			if (isset($this->sections[$section_id]) && isset($this->sections[$section_id]->groups[$group_id]) ) {
				$this->sections[$section_id]->groups[$group_id]->list[] = $option_name;

				$index = $option_name;
				$this->fields[$index]->type         = $type;
				$this->fields[$index]->label        = $label;
				$this->fields[$index]->text_after   = $text_after;
				$this->fields[$index]->text_before  = $text_before;
				$this->fields[$index]->description  = $description;
				$this->fields[$index]->status  		= $status;
				$this->fields[$index]->size   		= $size;
				$this->set_field_values($index, $values);
			}
			return ($index);
		}

		/**
		 * add_button
		 *
		 * Add button to the form
		 *
		 * @package EG-Forms
		 *
		 * @param 	string	$type		submit, reset
		 * @param	string	$name		name of the button
		 * @param	string	$value		value
		 * @return 	none
		 */
		function add_button($type, $name, $value, $callback='submit') {
			$index = sizeof($this->buttons);
			$this->buttons[$index]->type  = $type;
			$this->buttons[$index]->name  = $name;
			$this->buttons[$index]->value = $value;
			$this->buttons[$index]->callback = 'submit';
			if ($callback != 'submit' &&
				is_callable(array(&$this, $callback)) &&
				method_exists(& $this, $callback) ) $this->buttons[$index]->callback = $callback;
		}

		/**
		 * reset_to_default
		 *
		 * Reset form values to defaults
		 *
		 * @package EG-Forms
		 *
		 * @param 	array		$options	list of the options to update with the form value
		 * @param	array		$defaults	list of default values
		 * @return 	array				updated options
		 */
		function reset_to_defaults($options, $defaults) {
			return ($defaults);
		}

		/**
		 * get_form_values
		 *
		 * Add button to the form
		 *
		 * @package EG-Forms
		 *
		 * @param 	array		$options	list of the options to update with the form value
		 * @param	array		$defaults	list of default values
		 * @return 	array				updated options
		 */
		function get_form_values($options, $defaults, $update_options=FALSE) {

			// Which button to we use?
			$is_submitted = FALSE;
			foreach ($this->buttons as $button) {
				if ($button->type == 'submit') {
					if (isset($_POST[$button->name]) && $_POST[$button->name] = __($button->value, $this->textdomain)) {
						$is_submitted = $button->callback;
					break;
					}
				}
			}

			// Security ok and submit button hit
			if ($is_submitted === FALSE) {
				$new_options = $options;
			}
			else {

				if ( !wp_verify_nonce($_POST['_wpnonce'], $this->security_key) ) {
					echo '<div class="wrap">';
					if (function_exists('screen_icon')) screen_icon();
/*
					($this->id_icon!=''?'<div id="'.$this->id_icon.'" class="icon32"></div>':'').
*/
					echo ($this->title==''?'':'<h2>'.__($this->title, $this->textdomain).'</h2>').
					'<div id="message" class="error fade"><p>'.sprintf(__('Security problem. Try again. If this problem persist, contact <a href="%s">plugin author</a>.', $this->textdomain), $this->author_address).'</p></div>'.
					'</div>';

					die();
				}

				if ($is_submitted != 'submit') {
					$new_options = call_user_func(array(&$this, $is_submitted), $options, $defaults);
				}
				else {
					foreach ($this->fields as $key => $field) {
						if (isset($options[$key])) {
							if (isset($_POST[$key])) {
								$new_options[$key] = attribute_escape($_POST[$key]);
							}
							elseif ($field->type == 'checkbox') {
								$new_options[$key] = 0;
							}
							else {
								$new_options[$key] = $options[$key];
							}
						}
					}
				}
				if ($update_options !== FALSE && $update_options!='' && $new_options!=$options) update_option($update_options, $new_options);
			}
			return ($new_options);
		}

		/**
		 * display_field
		 *
		 * Add button to the form
		 *
		 * @package EG-Forms
		 *
		 * @param 	string	$option_name	id of the field to display
		 * @param	boolean	$group		is the field in a group or standalone
		 * @param 	array		$default_values	list of default values
		 * @return 	string				HTML code to display
		 */
		function display_field($option_name, $group, $default_values) {

			// if field doesn't exist => stop
			if (! isset($this->fields[$option_name]))
				return '';
			else {
				// Get field
				$field = $this->fields[$option_name];

				// in all the procedure: if group = TRUE, we are in a set of field. if group = FALSE, the current group contains ony one field
				$string = ($group?'<li>':'');
				switch ($field->type) {
					case 'text':
						if ($field->text_before!= '' || $field->text_after != '') {
							$string .= ($group?'<label for="'.$option_name.'">'.__($field->label, $this->textdomain):'').
								__($field->before, $this->textdomain).
								'<input type="text" class="'.$field->size.'-text" name="'.$option_name.'" id="'.$option_name.'" value="'.$default_values[$option_name].'" '.$field->status.'/> '.
								__($field->text_after, $this->textdomain).
								($group?'</label>':'');
						} else {
							$string .= ($group?'<label for="'.$option_name.'">'.__($field->label, $this->textdomain).'</label>':'').
								'<input type="text" class="'.$field->size.'-text" name="'.$option_name.'" id="'.$option_name.'"/ value="'.$default_values[$option_name].'" '.$field->status.'/> ';
						}
					break;

					case 'checkbox':
						if (! is_array($field->values)) {
							$string .= ($group?'<label for="'.$option_name.'">':'').
									'<input type="checkbox" name="'.$option_name.'" id="'.$option_name.'" value="1" '.($default_values[$option_name]==1?'checked':'').' '.$field->status.' /> '.
									__($field->label, $this->textdomain).
									($group?'</label>':'');
						}
						else {
							$string .= '<fieldset><legend class="hidden">'.__($field->label, $this->textdomain).'</legend>';
							foreach ($field->values as $key => $value) {
								$checked = ($default_values[$option_name]==1?'checked':'');
								$string .= ($group?'<label for="'.$option_name.'">':'').
									'<input type="checckbox" name="'.$option_name.'" id="'.$option_name.'" value="'.$key.'" '.$checked.' '.$field->status.'/> '.
									__($value, $this->textdomain).
									($group?'</label>':'').
									'<br />';
							}
							$string .= '</fieldset>';
						}
					break;

					case 'select':
						$string .= ($group?'<label for="'.$option_name.'">'.__($field->label, $this->textdomain):'').
								  '<select name="'.$option_name.'" id="'.$option_name.'" >';
						foreach ($field->values as $key => $value) {
							$selected = ($default_values[$option_name]==$key?'selected':'');
							$string .= '<option value="'.$key.'" '.$selected.'>'.__($value, $this->textdomain).'</option>';
						}
						$string .= '</select>'.($group?'</label>':'');
					break;

					case 'radio':
						$string .= '<fieldset><legend class="hidden">'.__($field->label, $this->textdomain).'</legend>';
						foreach ($field->values as $key => $value) {
							$checked = ($default_values[$option_name]==1?'checked':'');
							$string .= ($group?'<label for="'.$option_name.'">':'').
								'<input type="radio" name="'.$option_name.'" id="'.$option_name.'" value="'.$key.'" '.$checked.' '.$field->status.'/> '.
								__($value, $this->textdomain).
								($group?'</label>':'').
								'<br />';
						}
						$string .= '</fieldset>';
					break;
				}
				// Adding description
				if ($field->description) $string .= '<br /><span class="setting-description">'.__($field->description, $this->textdomain).'</span>';

				// Close the list (if group = TRUE only)
				$string .= ($group?'</li>':'');

				return $string;
			}
		}

		/**
		 * display_group
		 *
		 * Display a set of fields
		 *
		 * @package EG-Forms
		 *
		 * @param 	object	$group		group to display
		 * @param 	array		$default_values	list of default values
		 * @return 	none
		 */
		function display_group($group, $default_values) {

			// How many field do we have in this group?
			if (sizeof($group->list) == 1) {
				// Get the field
				$option_name = current($group->list);
				echo '<tr valign="top"><th scope="row">'.
					'<label for="'.$option_name.'">'.__($group->title, $this->textdomain).'</label>'.
					'</th><td>'.
					($group->header==''?'':'<p>'.__($group->header, $this->textdomain).'</p>').
					$this->display_field($option_name, FALSE, $default_values).
					($group->footer==''?'':'<p>'.__($group->footer, $this->textdomain).'</p>');
			} else {
				// Several field for this group
				echo '<tr valign="top"><th scope="row">'.__($group->title, $this->textdomain).'</th><td>'.
					($group->header==''?'':'<p>'.__($group->header, $this->textdomain).'</p>').
					'<fieldset><legend class="hidden">'.__($group->title, $this->textdomain).'</legend><ul>';
				// Displaying all of fields
				foreach ($group->list as $option_name) {
					echo $this->display_field($option_name, TRUE, $default_values);
				}
				echo '</ul></fieldset>'.
					($group->footer==''?'':'<p>'.__($group->footer, $this->textdomain).'</p>').
					'</td></tr>';
			}
		}

		/**
		 * display_section
		 *
		 * Display an entire form section
		 *
		 * @package EG-Forms
		 *
		 * @param 	object	$section		section to display
		 * @param 	array		$default_values	list of default values
		 * @return 	none
		 */
		function display_section($section, $default_values) {
			echo '<h3>'.__($section->title, $this->textdomain).'</h3>'.
				($section->header==''?'':'<p>'.__($section->header, $this->textdomain).'</p>').
				'<table class="form-table">'.
				'<tbody>';

			foreach ($section->groups as $group) {
				$this->display_group($group, $default_values);
			}
			echo '</tbody>'.
				'</table>'.
				($section->footer==''?'':'<p>'.__($section->footer, $this->textdomain).'</p>');
		}

		/**
		 * display_form
		 *
		 * Display the current form
		 *
		 * @package EG-Forms
		 *
		 * @param 	array		$default_values	list of default values
		 * @return 	none
		 */
		function display_form($default_values) {
			echo '<div class="wrap">'.
				($this->id_icon!=''?'<div id="'.$this->id_icon.'" class="icon32"></div>':'').
				($this->title==''?'':'<h2>'.__($this->title, $this->textdomain).'</h2>').
				($this->header==''?'':'<p>'.__($this->header, $this->textdomain).'</p>').
				'<form method="post" action="'.$this->url.'">';
				wp_nonce_field($this->security_key);

				// '<input type="hidden" name="eg_forms_nonce" value="'.wp_create_nonce($this->security_key).'" />';
				// '<input type="hidden" id="egs_options" name="egs_options" value="' . wp_create_nonce( 'eg-series-options' ) . '" />';

			foreach ($this->sections as $section) {
				$this->display_section($section, $default_values);
			}

			echo ($this->footer==''?'':'<p>'.$this->footer.'</p>').'<p>&nbsp;</p>';
			foreach ($this->buttons as $button) {
				echo '<input type="'.$button->type.'" class="button-primary" name="'.$button->name.'" value="'.__($button->value, $this->textdomain).'"/> ';
			}
			echo '</form>'.
				 '</div>';
		}

	} /* End of class EG_Forms */
} /* End of Class_exists */


/* Exemple of a form
$eg_form = new EG_Forms('Options de lecture', '', '', 'textdomain', '', "icon-options-general", 'toto', 'mailto:blog@georjon.eu' );
$id_section = $eg_form->add_section('', '', '');
	$id_group = $eg_form->add_group($id_section , 'La page d’accueil affiche', '', '');
		$eg_form->add_field($id_section, $id_group, 'checkbox', 'La page d’accueil affiche', '', '', '', 'option1');
		$eg_form->add_field($id_section, $id_group, 'select', 'Page d’accueil :', '', '', '', 'option2', array( '0' => '-Choisir-', '1' => 'A  propos', '2' => 'Contact') );
		$eg_form->add_field($id_section, $id_group, 'select', 'Page d’articles :', '', '', '', 'option3', array( '0' => '-Choisir-', '1' => 'A  propos', '2' => 'Contact') );
	$id_group = $eg_form->add_group($id_section , 'Les pages du blog doivent afficher au plus', '', '');
		$eg_form->add_field($id_section, $id_group, 'text', 'Les pages du blog doivent afficher au plus', '', 'articles', '', 'option4');
	$id_group = $eg_form->add_group($id_section , 'Les flux de syndication affichent les derniers', '', '');
		$eg_form->add_field($id_section, $id_group, 'text', 'Les flux de syndication affichent les derniers', '', 'articles', '', 'option5');
	$id_group = $eg_form->add_group($id_section , 'Pour chaque article, fournir ', '', '');
		$eg_form->add_field($id_section, $id_group, 'radio', 'Pour chaque article, fournir ', '', 'articles', '', 'option6', array( '0' => 'Le texte complet', '1' => 'L\'extrait'));
		$eg_form->add_field($id_section, $id_group, 'text', 'Encodage pour les pages et les flux RSS', '', '', 'L’encodage des caractères dans lequel vous écrivez votre blog (UTF-8 est recommandé)', 'option7');

$eg_form->add_button('submit', 'Save options', 'Save Options');
$eg_form->add_button('reset', 'cancel', 'Cancel');

$eg_form->set_field_values('option1', array ( '0' => 'Vos derniers articles', '1' => 'Une page statique (choisir ci-dessous)') );

$eg_form->display_form(array( 'option1' => 1,   'option2' => 1,    'option3' => 2,
                              'option4' => 'A', 'option5' => '10', 'option6' => 1,
							  'option7' => 'C')
					);
*/

?>