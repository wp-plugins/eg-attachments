=== Plugin Name ===
Contributors: Emmanuel Georjon
Donate link: 
Tags: post, attachment
Requires at least: 2.5.0
Tested up to: 2.7.0
Stable tag: 1.1.4

This plugin add a shortcode to display the list of attachments of a post, with icon and details. EG-Attachments is "TinyMCE integrated".

== Description ==

EG-Attachments add a new shortcode attachements. This shortcode can be used with many options.
But you don't need to know all of these options, because the plugin is "TinyMCE integrated" : from the post editor, just click on the EG-Attachments button, and a window allows you to choose documents to display, title of the list, size of icons ... Nothing to learn.

But you can insert the **shortcode** by hand if you want, or use it in a template using the **do_shortcode** function.

The list includes for each attachments:

* Icon, 
* Title, 
* Description, 
* Size.

Options are 

* Icon size, 
* Sort order, 
* Document type,
* Document list,
* Title of the list
* Label of each document

== Installation ==

# Installation #
* The plugin is available for download on the WordPress repository,
* Once downloaded, uncompress the file eg-attachments.zip,
* Copy or upload the uncompressed files in the directory wp-content/plugins in your WordPress platform
* Activate the plugin, in the administration interface, through the menu Plugins

The plugin is now ready to be used.
You can also use the WordPress 2.7 features, and install the plugin directly from the WordPress interface.

# Usage #

* When editing a post, you can see a *paper clip* button in TinyMCE bar. 
* Click on this button, choose the options, and click insert.
* In a template file, add the following code: `<?php do_shortcode('[attachments *options*]'); ?>`

The shortcode options are:

* **size:** size of the icon. Values: large, medium or small. Default: large,
* **doctype:** type of documents to display. Values: image or document. Defaults: document,
* **docid** list of attachments' id (comma separated) you want to display. Default: nothing to display all attachments,
* **orderby:** sort option. Values: ID, post_title, and ASC or DESC. Default: `post_title ASC`.
* **title:** title to display before the list. Default: '',
* **titletag:** tag to add before and after the title. Default: h2
* **label** label of each document. Values: filename, doctitle. Default: filename. Option available for size=small or size=medium only.

**Exemple:** `[attachments size=medium doctype=document title="Attachments" titletag=h2 orderby="post_title ASC"]`

== Frequently Asked Questions ==

= How can I display attachments by modifying my templates? =
In your `single.php` file, add the following code: `<?php echo do_shortcode('[attachments *shortcode options*]'); ?>`

= How can I change the styles? =
The stylesheet is named eg-attachments.css, and can be stored in two places:

* In the plugin directory,
* In the directory of your current theme

Use FTP client and text editor to modify it.

= I would like to change the icons =
Just copy/upload your own icons in the `images` subdirectory of the plugin.
Size of icons must be 52x52 or 48x48. Name of icons must be the mimetype or file extension.

== Screenshots ==

1. List of attachments sorted by name, with small icons,
2. List of attachments, with medium icons,
3. List of attachments, with large icons,
4. EG-Attachments button in the TinyMCE toolbar,
5. Insert attachments window.

== Version history ==

* Version 1.1.4 - Feb 21th, 2009
	* Bug fix: plugin didn't work properly with PHP 4

* Version 1.1.3 - Feb 16th, 2009
	* Bug fix: in some cases, the icon didn't appear in the TinyMCE button bar

* Version 1.1.2 - Feb 13th, 2009 (not published)
	* Enable cache management again

* Version 1.1.1 - Feb 11th, 2009
	* Disable temporarily the management of the cache
	
* Version 1.1.0 - Feb 9th, 2009
	* Add option (label) to choose file label (filename or document title)
	* Sanitize title
	* Improve some translations (french)
	* Internal changes

* Version 1.0.1 - Feb 2nd, 2009
	* Just update readme.txt file (author and plugin URL)

* Version 1.0 - Feb 2nd, 2009
	* First release

== Licence ==

This plugin is released under the GPL, you can use it free of charge on your personal or commercial blog.

== Translations ==

The plugin comes with French and English translations, please refer to the [WordPress Codex](http://codex.wordpress.org/Installing_WordPress_in_Your_Language "Installing WordPress in Your Language") for more information about activating the translation. If you want to help to translate the plugin to your language, please have a look at the eg_attachments.pot file which contains all defintions and may be used with a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) (Windows).