=== EG-Attachments ===
Contributors: Emmanuel Georjon
Donate link: http://www.emmanuelgeorjon.com/donate?plugin=eg-attachments
Tags: posts, attachments, shortcode, widgets
Requires at least: 3.3
Tested up to: 3.5
Stable tag: 1.9.4.4

This plugin add a shortcode to display the list of attachments of a post, with icon and details. EG-Attachments is "TinyMCE integrated".

== Description ==

EG-Attachments add a new shortcode attachements. This shortcode can be used with many options.
But you don't need to know all of these options, because the plugin is "TinyMCE integrated" : from the post editor, just click on the EG-Attachments button, and a window allows you to choose documents to display, title of the list, size of icons ... Nothing to learn.

You can insert the **shortcode** by hand if you want, or use it in a template using the **do_shortcode** function.

An another way is to activate option **auto shortcode** that automaticaly adds the list of attachments, where you want in your posts or pages.

The list includes, for each attachments:

* Icon,
* Title,
* Description,
* Caption,
* Type,
* Date,
* Size.

Options are

* Icon size,
* Sort order,
* Document type,
* Document list,
* Title of the list,
* Label of each document,
* Fields to display (caption, description, date, type, ...),
* Type of link to attachments: permalink, or direct link to the file,
* Force "Save as" (rather than display document),

Since the version 1.5.0, **EG-Attachments** plugin counts the number of clicks occuring on each attached document.

= Plugin's Official Site =

* [Overview](http://www.emmanuelgeorjon.com/eg-attachments-1233/)
* [Docs](http://www.emmanuelgeorjon.com/eg-attachments-documentation-4733/)
* [Support & FAQ](http://www.emmanuelgeorjon.com/eg-attachments-support-4735/)
* [Changelog](http://www.emmanuelgeorjon.com/eg-attachments-changelog-4737/)

= Contributions =

Thanks to

* [Dave](http://www.jxs.nl/) for the "custom style" feature,
* [Rebekah](http://www.learntowebdesign.com/) for her [video tutorial](http://www.learntowebdesign.com/2009/12/placing-attachments-wordpress-post-page/)
* [Luca Maida](http://www.qsin.it/) for his comments on HTML standards compliance
* [Roberto Scano](http://robertoscano.info/) for his help on debugging, and ideas for new features (tags for example)
* David Lingren for his help on debugging

= Translations =

The plugin comes up with 10 translations. Thanks to the following people for their contributions:

* Arabic (AR) - Mahmoud Ahmed,
* Belarusian (BY) - Fatcow,
* Czech (CZ) - Josef &#353;abata,
* Dutch (NL) - [Rene at WP webshop](http://wpwebshop.com/premium-wordpress-themes/),
* French (FR) - [Emmanuel](http://www.emmanuelgeorjon.com/),
* German (DE) - [DesignContext](http://www.designcontest.com/),
* Italian (IT) - [Luca Maida](www.qsin.it) and [Roberto Scano](http://robertoscano.info/),
* Spanish (ES) - [David Arinez](http://www.codeeta.com/),
* Polish (PL) - [Mariusz Szatkowski](http://www.trojmiasto.us/),
* Romanian (RO) - [Armand Coveanu](http://caveatlector.eu/),
* Swedish (SE) - [Jonas Floden](http://www.koalasoft.se/)

If you want to help to translate the plugin to your language, please have a look at the eg_attachments.pot file which contains all definitions and may be used with a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) (Windows).

If you have created your own language pack, or have an update of an existing one, you can send [gettext .po and .mo files](http://codex.wordpress.org/Translating_WordPress) to me so that I can bundle it into the plugin.

== Installation ==

= Installation =
* The plugin is available for download on the WordPress repository,
* Once downloaded, uncompress the file eg-attachments.zip,
* Copy or upload the uncompressed files in the directory wp-content/plugins in your WordPress platform
* Activate the plugin, in the administration interface, through the menu Plugins

The plugin is now ready to be used.
You can also install the plugin directly from the WordPress interface.

Then you can go to menu **Settings / EG-Attachments** to set plugin parameters

This plugin was tested on WordPress 3.1, and up to 3.3.1.

= Update =

When a new version is available, options of the plugins are updated during the activation.
So during the upgrade procedure, please desactivate / reactivate the plugin, in order to ensure that options are properly modified.

= Usage =

Four ways to include the list of attachments into a post:
* With the *paper clip* button in TinyMCE bar. Click on this button, choose the options, and click insert,
* With the shortcode [attachments *options* ]
* With the automatic shortcode: go to the **Settings / EG-Attachments**, and choose to activate the auto-shortcode. The list of attachments will be added to your post automatically.
* In a template file, add the following code: `<?php do_shortcode('[attachments *options*]'); ?>`

Since 1.7.4, the plugin can display, in the admin interface, the list of attachments of the post being edited. Go to **Settings / EG-Attachments**, chapter *Administration Interface* for further details.

The shortcode options are:

* **size**: size of the icon. Values: large, medium, small or custom. Default: large,
* **doctype**: type of documents to display. Values: image or document. Defaults: document,
* **docid** list of attachments' id (comma separated) you want to display. Default: nothing to display all attachments,
* **id**: id of the post we want to display attachments
* **orderby**: sort option. Values: title, caption, description, file name, size, date, type, menu_order and ASC or DESC. `ASC`is the default sort order. Default: `title ASC`.
* **title**: title to display before the list. Default: '',
* **titletag**: tag to add before and after the title. Default: h2
* **label** label of each document. Values: filename, doctitle. Default: filename. Option available for size=small or size=medium only.
* **fields**, list of fields to display. Values: Document label, Title, Caption, Description, File name, Size, Small size, Date, Type, or a set of values such as "caption,description" (comma separated).
* **force_saveas** forces the browser to show the dialog box (Run / Save as) rather than display attachment. Values: true or false. Default: the default value is defined in the **Settings page** in administration interface.
* **icon** specify if icons will be displayed or not. Default value: 1 or TRUE. If value is 0 or FALSE, list displayed will be ul/li (html simple list) rather than dl/dt/dd (definition list).
Two specific keywords can be used with **docid** option: **first** and **last** allow to display the first and the last attachment of list. Be careful, **first** or **last** can change according the sort option ! These keywords must be used alone. You can have syntax such as: first,10,11.
* **limit**: choose the number of attachements you want to display. Default: all attachments are displayed
* **nofollow**: add the attribut "nofollow" to the link, if value is set to 1 or TRUE. Default nofollow=0
* **display_label**: for size=small only. Allow to display label of fields, when value is set to 1. Default display_label=0
* **logged_users** authorizes access to the file, to logged users only, or to all users. Possible values: 0, all users can visualize or download attachments, and 1, only logged users can access to attachments. Default value: the default value is defined in the **Settings page** in administration interface.
* **tags** allows you to select attachments according tags (post tags). Syntax: tags=tag1,tag2.

**Example 1:** `[attachments size=medium doctype=document title="Attachments" titletag=h2 orderby="title"]`

**Example 2:** `[attachments size=large title="Documents" titletag=h3 orderby="mime DESC"]`

**Example 3:** `[attachments title="Books and DVD Reviews" orderby="date DESC" tags="books,dvd"]`

= Some explanations about *General behavior of shortcodes =

In the menu **Settings / EG-Attachments**, you will find a section named *General behavior of shortcodes*.
The options in the section are

* applied to all shortcodes (automatic or manually inserted into posts),
* used as options for the automatic shortcode,
* used as default value for the shortcode manually inserted into posts.

**Example:** if you check the option *Force "Save As" when users click on the attachments*, you force download for all attachments displayed by auto-shortcodes, and manual shortcode, except if you specify `force_saveas` in a shortcode option.

The defaults parameters of auto-shortcode are:

* Small size: label, size,
* Medium size: label, caption, size,
* Large size: title, file name, caption, size.

Where **label** is: title or file name according selected option.

= Statistics =

Just activate the **clicks counter** in the menu *Settings/EG-Attachments*, and then go to the menu **Tools / EG-Attachments statistics** to see how many clicks you have, for each document.

== Frequently Asked Questions ==

= How to display documents that are not related to the current post? =
You have two ways:

* Specify, with parameter **id**, the post to which documents are attached,
* With the parameter **docid**, give the list of documents you want to display.

= During post edition, how can I see the list of attachments that will be displayed? =
In the menu **Settings / EG-Attachments**, in the chapter *Administration interface*, you can choose to show or hide, a metabox that displays attachments of the post being edited.

= Could I have some examples of the usage of `orderby` shortcode option? =

* **orderby="mime DESC"** to sort by mime type descending,
* **orderby=date** or **orderby="date ASC"** to sort by date ascending,

= How can I display attachments by modifying my templates? =
In your `single.php` file, add the following code: `<?php echo do_shortcode('[attachments *shortcode options*]'); ?>`

= How can I change the styles? =
The stylesheet is named eg-attachments.css, and can be stored in two places:

* In the plugin directory,
* In the directory of your current theme

= I would like to change the icons =
Just copy/upload your own icons in the `images` subdirectory of the plugin.
Size of icons must be 52x52 or 48x48. Name of icons must be the mimetype or file extension.

= I click on a document to test statistics, but counters stay flat =
EG-Attachments uses a *cache system* to build statistics, avoiding to launch heavy queries, each time you want to see statistics. The cache duration is 15 minutes. If you want to test statistics, click on several attached files, and then wait 15 minutes, your clicks will appear.

== Screenshots ==

1. List of attachments sorted by name, with small icons,
2. List of attachments, with medium icons,
3. List of attachments, with large icons,
4. EG-Attachments button in the TinyMCE toolbar,
5. Insert attachments window,
6. Options page in administration interface,
7. Global statistics page,
8. Detailed statistics page.

== Changelog == 

= Version 1.9.4.4 - January 20th, 2013 =
* Bug fix: message during activation *Warning: Creating default object from empty value in eg-plugin.inc.php on line 804*

= Version 1.9.4.3 - March 2nd, 2011 =
* Bug fix: option *where* for the auto shortcode didn't work properly,
* Bug fix: conflict with custom post type
* Change: updated translations for Czech, and Italian languages,
* Change: internal librairies updates.

= Version 1.9.4.2 - Feb 9th, 2011 =
* Bug fix: option *Target=_blank*, and shortcode parameter *target*,  didn't work
* Bug fix: parameter *nofollow* in shortcode didn't work properly
 
= Version 1.9.4.1 - Feb 8th, 2011 =

* New: Czech translation,
* Change: Updated italian translation,
* Bug fix: Tags without posts weren't displayed 
* Bug fix: *Target=_blank* option didn't work
* Bug fix: Options not updated during the automatic upgrade procedure (since WP 3.1, see [Function Reference/register activation hook
](http://codex.wordpress.org/Function_Reference/register_activation_hook#Changelog))

= Version 1.9.4 - Feb 2nd, 2011 =

* Bug fix: EG-Attachments button in the TinyMCE editor doesn't work,
* Bug fix: some conflicts with plugins using filters in the `get_posts` function,
* Bug fix: order_by=menu_order didn't work properly,
* New: manage "custom post type",
* New: ability to add `target=blank` attribute to the links,
* New: ability to add a path to store your own icons,
* New: Select attachments with tags in the widget,
* Change: internal librairies updates

= Version 1.9.3 - Jan 24th, 2011 =

* Bug fix: broken link to lock.png image,
* New: translation to Polish (thanks to Mariusz Szatkowski),
* New: translation to Arabic (thanks to Mahmoud Ahmed),
* New: Ability to open or close comments for attachments,
* New: Ability to associate attachments with tags, and select attachments according tags,
* New: Add an optional menu in the admin menu bar,
* New: ability to choose the size of the icon with custom format,
* New: add "menu_order" as sort key for auto-shortcode,
* Change: documentation updated
* Change: internal librairies updates because of bug fix, and recommendations about enqueuing styles and scripts (for WP 3.3)

= Version 1.9.2 - Nov 1st, 2011 =

* Bug fix: settings was not saved properly

= Version 1.9.1 - Oct 27th, 2011 =

* Bug fix: error message from the file eg-attachments-settings.inc.php
* Change: internal librairies

= Version 1.9.0 - Oct 25th, 2011 =

* New: choose type of link for attachments (permalink, file, or direct),
* New: translation in German (Thanks to [DesignContext](http://www.designcontest.com/) )
* New: attachments has the same security level than post to which they are attached:
	* if the post is private, attachments are considered as private, and cannot be read or download if user is not logged
	* if the post is passwordd protected, attachments can be accessed only if the users provide the right password.
* Bug fix: HTML validation error,
* Bug fix: Bad statistics values,
* Bug fix: Statistics: Bad alignments for month (data for September were displayed in the column October)
* Bug fix: Bad HTML syntax, when titletag is set to empty string,
* Bug fix: Error when custom is selected, and no attachment are to be displayed,
* Bug fix: date format option didn't applied,
* Bug fix: cannot display field ID,
* Bug fix: sort issue for ID ASC, or ID DESC,
* Bug fix: docid=first and docid=last didn't work,
* Bug fix: widget didn't display the right attachments,
* Bug fix: option logged_users_only didn't work in widget,
* Bug fix: fatal error during upgrade,
* Change: encode url to prevent error with file name containing some specific characters
* Change: New options page,
	* Use boxes that can be collapsed, opened, moved, ...
	* Group some sections and move fields to *General behavior of shortcodes*,
* Change: reduce the size of stylesheets,
* Change: align widgets options, with the shortcode parameters,
* Change: updated documentation (this file), and screenshots,
* Change: update plugin library,

= Version 1.8.6 - Aug 27th, 2011 =

* Bug fix: force download option doesn't work when the PHP **fopen wrappers** option is disabled
* New: add %DATE% keyword is available now for the custom format.

= Version 1.8.5 - Aug 10th, 2011 =

* Bug fix: zip files were corrupted after download (with Internet explorer, and zlib, or gzip enable on server).
* Bug fix: sort order didn't work when not specify in uppercase,
* Bug fix: cannot sort by ID.
* Bug fix: uninstallation didn't run properly
* Bug fix: exclusion list for statistic didn't work properly
* Change: replace some PHP depredicated functions

= Version 1.8.4 - July 30th, 2011 =

* New: add ability to display TYPE in custom format
* Bug fix: now, by default, label of fields are not displayed when size is small (same behavior than version 1.7.4)
* New: for small size, you can choose to display labels or not (default not).

= Version 1.8.3 - July 19th, 2011 =

* Bug fix: Remove some debug information. Sorry

= Version 1.8.2 - July 18th, 2011 =

* New: parameter limit can be modified with the button
* Bug fix: SSL compatibility,
* Bug fix: parameter limit doesn't work properly with auto-shortcode,
* Bug fix: bad url when use standard URL (http://host/path/?p= ...)
* Change: file extension appears now in the file name
* Change: updated POT file
* Change: updated french and italian translations
* Change: internal libraries.

= Version 1.8.1 - July 12th, 2011 =

* Bug fix: no field displayed when fields=none. Now, default fields are displayed

= Version 1.8.0 - July 11th, 2011 =

In this version, I rewrote the module displaying shortcode content. Three targets:

* Simplify the code,
* Clarify fields display (options are more consistent now)
* Remove bugs.

List of changes and bug fixes:

* Bug fix: logged_users didn't work with size=medium,
* Bug fix: link didn't work when title was set to doctitle,
* Bug fix: PHP parse error with PHP version lower than 5.1.2,
* Bug fix: title was displayed, even if the list is empty,
* New: parameter **limit** for shortcode. Limit is the number of attachments to display. Default: -1, all documents,
* New: fields parameter accepts now: Document label, Title, Caption, Description, File name, Size, Small size, Date, Type,
* New: orderby parameter accepts now: Title, Caption, Description, File name, Size, Date, Type,
* New: automatic shortcode won't be displayed if a manual shortcode exists in the post,
* New: Choose if you want to add the 'nofollow' attribute or not,
* Change: run with WordPress up to 3.2,
* Change: internal libraries.

= Version 1.7.4 - Sept 27th, 2010 =

* New: Possibility to display a metabox under the post editor, to list attachments of the post being edited,
* Bug fix: links of attachments are encoded (XHTML compliant),

= Version 1.7.3.1 - Sept 19th, 2010 =

* New: Optional load of the stylesheet,
* New: the option *size=custom*, is working properly now, for both automatic and manual shortcode. Default values are common.
* Bug fix: shortcode option *order_by* didn't work properly,
* Bug fix: unexpected comma in the list of field when shortcode is build with the TinyMCE EG-attachment button,
* Bug fix: stats didn't work,
* Bug fix: some errors in french translation,
* Bug fix: fields choice, suppress description from medium size,
* Bug fix: error in the widget, when fields *caption* and *description* was checked together,
* Bug fix: in some cases, file size was not displayed,
* Bug fix: links of attachments are encoded (XHTML compliant),
* Bug fix: %FILE_SIZE% parameter didn't work in custom format
* Change: internal libraries.

= Version 1.7.2 - July 28th, 2010 =

* New: Two new translations Spanish and Dutch.

= Version 1.7.1 - June 07th, 2010 =

* Bug fix: Some errors in the readme.txt file,
* Bug fix: Attachments was displayed in the widget, evenif post was password restricted,
* Bug fix: widget didn't displayed in a page (only post).

= Version 1.7.0 - May 20nd, 2010 =

* Bug fix: some errors in french translation
* Bug fix: statistics menu doesn't appear
* Change: Some generated HTML code was not valid (thanks to [Roberto Scano](http://robertoscano.info/) )
* New: the parameter *id* allows to specify the id of the post we want to display attachments.
* New: widgets displaying attachments of the current post
* New: Italian translation (thanks to Luca Maida)
* New: Swedish translation (thanks to Jonas Floden)

= Version 1.6.0 - Jan 26th, 2010 =

* Bugfix: Fatal error during first activation
* New: ability to choose the location of the auto-shortcode (at the beginning of the post, between the excerpt and the content, or at the end of the post)
* New: Custom style. Build your own style to list attachments (thanks to [Dave](http://www.jxs.nl/) )

= Version 1.5.2 - Dec 20th, 2009 =

* Bugfix: some features was desactivated in the 1.5.1 version;
* Change: internal libraries

= Version 1.5.1 - Dec 20th, 2009 =

* Bugfix: in the "edit posts" page, all fields was empty when using EG-Attachments.

= Version 1.5.0 - Sept 21st, 2009 =

* New: click-tracker,
* New: translation in Belarusian (thanks to Fatcow),
* Bugfix: try to fix bug that occurs when file names contain non alphabetical characters,
* Change: Internal library update.

= Version 1.4.3 - Sept 14, 2009 =

* Bugfix: Manage boolean values (true/false), in the same manner than 0/1.
* Change: options change, and internal libraries change.

= Version 1.4.2 - Sept 1, 2009 =

* New: auto-shortcode options can be used as default values for the TinyMCE button,

= Version 1.4.1 - Aug 24th, 2009 =

* New: Add parameters to the shortcode window in TinyMCE editor
* Bugfix: auto-shortcode behavior with *logged_users* parameter
* Change: Internal library update.

= Version 1.4.0 - Aug 14th, 2009 =

* Bugfix: Force Saveas option doesn't work,
* New: ability to restrict attachments to logged users,
* Change: Improve SaveAs feature,
* Change: Internal library update.

= Version 1.3.1 - July 18th, 2009 =

* Bugfix: Auto-shortcode displays attachments evenif post is password protected
* Bugfix: in readme.txt, default value of *logged_users* option.
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
