=== Plugin Name ===
Contributors: Emmanuel Georjon
Donate link: http://www.emmanuelgeorjon.com/
Tags: post, attachment
Requires at least: 2.6.0
Tested up to: 2.8.4
Stable tag: 1.4.0

This plugin add a shortcode to display the list of attachments of a post, with icon and details. EG-Attachments is "TinyMCE integrated".

== Description ==

EG-Attachments add a new shortcode attachements. This shortcode can be used with many options.
But you don't need to know all of these options, because the plugin is "TinyMCE integrated" : from the post editor, just click on the EG-Attachments button, and a window allows you to choose documents to display, title of the list, size of icons ... Nothing to learn.
This feature is only available since WordPress 2.7 and above.

You can insert the **shortcode** by hand if you want, or use it in a template using the **do_shortcode** function.

An another way is to activate option **auto shortcode** that adds automaticaly lists of attachments at the end of posts or pages.

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
* Title of the list,
* Label of each document,
* Description field to display (caption and/or description),
* Force "Save as" (rather than display document),
* Restrict access of the attachments to the logged users.

== Installation ==

= Installation =
* The plugin is available for download on the WordPress repository,
* Once downloaded, uncompress the file eg-attachments.zip,
* Copy or upload the uncompressed files in the directory wp-content/plugins in your WordPress platform
* Activate the plugin, in the administration interface, through the menu Plugins

The plugin is now ready to be used.
You can also use the WordPress 2.7 features, and install the plugin directly from the WordPress interface.

Then you can go to menu **Settings / EG-Attachments** to set plugin parameters

This plugin was tested on WordPress 2.6.x, 2.7.x and up to 2.8.4.
About WordPress MU: the plugin is running properly on version 2.8.x Assume that it run also with WordPress MU 2.6 and 2.7.

= Usage =

* When editing a post, you can see a *paper clip* button in TinyMCE bar. 
* Click on this button, choose the options, and click insert.
* In a template file, add the following code: `<?php do_shortcode('[attachments *options*]'); ?>`

The shortcode options are:

* **size:** size of the icon. Values: large, medium or small. Default: large,
* **doctype:** type of documents to display. Values: image or document. Defaults: document,
* **docid** list of attachments' id (comma separated) you want to display. Default: nothing to display all attachments,
* **orderby:** sort option. Values: ID, title, date, mime and ASC or DESC. `ASC`is the default sort order. Default: `title ASC`.
* **title:** title to display before the list. Default: '',
* **titletag:** tag to add before and after the title. Default: h2
* **label** label of each document. Values: filename, doctitle. Default: filename. Option available for size=small or size=medium only.
* **fields**, list of fields to display. Values: none, caption, description, or a set of values such as "caption,description" (comma separated). Default: caption (same behavior than previous version)
* **force_saveas** forces the browser to show the dialog box (Run / Save as) rather than display attachment. Values: true or false. Default: the default value is defined in the **Settings page** in administration interface.
* **icon** specify if icons will be displayed or not. Default value: 1 or TRUE. If value is 0 or FALSE, list displayed will be ul/li (html simple list) rather than dl/dt/dd (definition list).
Two specific keywords can be used with **docid** option: **first** and **last** allow to display the first and the last attachment of list. Be careful, **first** or **last** can change according the sort option ! These keywords must be used alone. You can have syntax such as: first,10,11.
* **logged_users** authorizes access to the file, to logged users only, or to all users. Possible values: 0, all users can visualize or download attachments, and 1, only logged users can access to attachments. Default value: 0.

**Example 1:** `[attachments size=medium doctype=document title="Attachments" titletag=h2 orderby="title"]`

**Example 2:** `[attachments size=large title="Documents" titletag=h3 orderby="mime DESC"]`

= Some explanations about *General behavior of shortcodes =

In the menu **Settings / EG-Attachments**, you will find a section named *General behavior of shortcodes*.
The options in the section are 

* applied to all shortcodes (automatic or manually inserted into posts),
* used as options for the automatic shortcode,
* used as default value for the shortcode manually inserted into posts.

**Example:** if you check the option *Force "Save As" when users click on the attachments*, you force download for all attachments displayed by auto-shortcodes, and manual shortcode, except if you specify `force_saveas` in a shortcode option.

== Frequently Asked Questions ==

= Could I have some examples of the usage of `orderby` shortcode option? =

* **orderby="mime DESC"** to sort by mime type descending,
* **orderby=date** to sort by date ascending,

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
5. Insert attachments window,
6. Options page in administration interface.

== Changelog ==

= Version 1.4.0 - Aug 14th, 2009 =

* Bugfix: Force Saveas option doesn't work,
* New: ability to restrict attachments to logged users,
* Change: Improve SaveAs feature,
* Change: Internal library update.

= Version 1.3.1 - July 18th, 2009 =

* Bugfix: Auto-shortcode displays attachments evenif post is password protected
* Change: Changes on internal libraries

= Version 1.3.0 - June 22th, 2009 =

* Bugfix: All users can use the EG-Attachments button in the TinyMCE editor.	

= Version 1.2.9 - June 20th, 2009 =

* Bugfix: French translation
* New: Delete options during uninstallation
* New: Support of *hidepost* plugin
* Change: Changes on internal libraries

= Version 1.2.8 - May 16th, 2009 =

* New: Option to display icons or not
* Change: Internal change of libraries

= Version 1.2.7 - Mar 30th, 2009 =

* Bugfix: Requirements warning message with Wordpress Mu
* Change: Attachments lists are enclosed in HTML div tag

= Version 1.2.6 - Mar 26th, 2009 =

* Bugfix: Attachments displayed several times when medium size is choosen.

= Version 1.2.5 - Mar 19th, 2009 =

* Bugfix: Error when neither "caption" nor "description" are displayed

= Version 1.2.4 - Mar 17th, 2009 =

* Bugfix: Don't display title when list of attachments is empty (auto shortcode function)
* Bugfix: Error when displaying options page
* Bugfix: Translation to french not complete
* New: Field choice (caption and/or caption)
* Change: Update internal library

= Version 1.2.3 - Mar 5th, 2009 =

* Bugfix: Mistake during SVN transfer from my PC to the wordpress repository

= Version 1.2.2 - Mar 5th, 2009 =

* Bugfix: Bad behavior with doctype

= Version 1.2.1 - Mar 4th, 2009 =

* Bugfix: Sort key and sort order didn't work properly,
* New: Sort key and sort order for the automatic shortcode

= Version 1.2.0 - Mar 2nd, 2009 =

* Bugfix: Translation of TB, MB, kB, B in french,
* New: Add automatically the list of attachments in the post content (optional),
* New: Force the "Save-as" dialog box (optional and experimental)

= Version 1.1.4 - Feb 21th, 2009 =

* Bugfix: Plugin didn't work properly with PHP 4

= Version 1.1.3 - Feb 16th, 2009 =

* Bugfix: In some cases, the icon didn't appear in the TinyMCE button bar

= Version 1.1.2 - Feb 13th, 2009 (not published) =

* Bugfix: Enable cache management again

= Version 1.1.1 - Feb 11th, 2009 =

* Bugfix: Disable temporarily the management of the cache
	
= Version 1.1.0 - Feb 9th, 2009 =

* Bugfix: Sanitize title
* Bugfix: Improve some translations (french)
* New: Add option (label) to choose file label (filename or document title)
* Change: Update internal library

= Version 1.0.1 - Feb 2nd, 2009 =

* Change: Just update readme.txt file (author and plugin URL)

= Version 1.0 - Feb 2nd, 2009 =

* New: First release

== Licence ==

This plugin is released under the GPL, you can use it free of charge on your personal or commercial blog.

== Translations ==

The plugin comes with French and English translations, please refer to the [WordPress Codex](http://codex.wordpress.org/Installing_WordPress_in_Your_Language "Installing WordPress in Your Language") for more information about activating the translation. If you want to help to translate the plugin to your language, please have a look at the eg_attachments.pot file which contains all defintions and may be used with a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) (Windows).