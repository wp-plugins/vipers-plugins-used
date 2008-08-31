=== Viper's Plugins Used ===
Contributors: Viper007Bond
Donate link: http://www.viper007bond.com/donate/
Tags: statistics, plugins
Stable tag: trunk

Allows you to display alphabetically what plugins you have enabled on your blog in either a table or unordered list. Also allows you to set custom descriptions for the plugins in the output.

== Description ==

Ever want to show your readers what plugins you have enabled on your site? If so, this plugin is for you. It allows you to list out all activated plugins in a table or unordered list as well as customize their descriptions.

To see it in action, check out [the author's website](http://www.viper007bond.com/about/plugins-used/).

_Last Updated: September 15th, 2005_

== Installation ==

Upload `vipers_pluginsused.php` to your `/wp-content/plugins/` folder and activate it from the admin area.

In order to display your plugins list, you'll have to use a custom Page template. Unfortunately, this is not the easiest thing in the world to do for a novice user. I have plans to make the installation process much, much easier in the future by switching to placeholders and/or BBCode, but for now, you'll have to follow these instructions. But don't worry, they should be simple to follow.

http://codex.wordpress.org/Pages#Creating_your_own_Page_Templates

1. Browse to your theme's folder (`/wp-content/themes/[theme name]/`) and download `page.php` (if it doesn't exist, use `index.php`).

2. Rename the file to something like `page_pluginsused.php` and open it up with a text editor. See [the WordPress Codex](http://codex.wordpress.org/Editing_Files) if you need help doing this. You can also just upload the file now if you wish and [edit it via the admin area](http://codex.wordpress.org/Editing_Files#Using_the_File_Editor).

3. Add the following to the top of the file to give the new Page template a name and to initiate the plugin:

`<?php
/*
Template Name: Plugins Used
*/
$viperspluginsused = new viperspluginsused();
?>`

4. Find the `the_content()` call. We will be adding the plugin list output after this.


Output As A Table
=================

To output a table, use this code:

`<?php $viperspluginsused->output_table(); ?>`

The function parameters are:

* Table Properties (default is: width="100%" border="1" cellpadding="3" cellspacing="3") -- the other properties of the <table> tag. Set to a space if you want no other properties. Example value: id="pluginstable" which results in <table id="pluginstable">

* Display Description (default = TRUE) -- makes a column for the plugin descriptions and outputs them

* Display Version (default = TRUE) -- makes a column for the plugin versions and outputs them

* Display Author (default = TRUE) -- makes a column for the plugin authors and outputs them

Example code to list all plugins in a "[ Plugin | Author ]" format with a table ID of "pluginstable":

`<?php $viperspluginsused->output_table('id="pluginstable"', FALSE, FALSE); ?>`

Besides the overall table's ID/class/whatever which you can control via the function parameters, additional classes are placed into the table automatically:

* pluginheader -- the class assigned to the row which contains the column titles, you can also use "th" to control this

* pluginrow / pluginrowalt -- the classes assigned to the plugin rows themselves, starts off with "pluginrow"

Some example CSS:

`#pluginstable {
	width: 100%;
	border-spacing: 3px;
}

#pluginstable td {
	border: 1px solid black;
	padding: 3px;
}

#pluginstable th {
	padding: 3px;
}`


Output As A Table
=================

To output an unordered list, use this code:

`<?php $viperspluginsused->output_list(); ?>`

The parameters are:

* Display ULs (default = TRUE) -- outputs the <ul>'s needed. You can turn this off if you want to add other items to the list or whatever. Note that if you turn it off, you'll need to use your own <ul> tags.

* Display Version (default = TRUE) -- outputs the plugin's version

* Display Author (default = TRUE) -- outputs the author's name, as a link if an author URL exists

* Display Description (default = FALSE) -- outputs the description of the plugin

Example code to list all plugins in a "Plugin by Author" format:

`<?php $viperspluginsused->output_list(TRUE, FALSE); ?>`


Get Count of Activated Plugins
==============================

If you'd like to display a count of how many plugins you have activated, use this code:

`<?php echo $viperspluginsused->plugincount(); ?>`

Note that YOU MUST ECHO THIS as this function only returns the value, it does not display it. This is so that you can use it within PHP code if you wish.

Example usage:

`I currently have <?php echo $viperspluginsused->plugincount(); ?> plugins activated on my site.`


== Frequently Asked Questions ==

= I can't figure out how to install this. It's hard! Can you do it for me? =

Sorry, no, I'm far too busy. I hope to make the installation process much simplier in the future.


== Screenshots ==

1. Plugin's page in the admin area listing all activated plugins

2. Edit displayed plugin description screen


== Advanced Usage ==

To get a PHP array containing details of all activated plugins, use this code:

`$somevar = $viperspluginsused->plugindata;`

That'll return an array in this format:

`Array
(
    [Viper's Plugins Used] => Array
	(
	    [plugin_uri] => http://www.viper007bond.com/wordpress-plugins/vipers-plugins-used/
	    [description] => Allows you to display alphabetically what plugins you have enabled on your blog in either a table or unordered list.
	    [author_name] => Viper007Bond
	    [author_uri] => http://www.viper007bond.com/
	    [version] => 1.0
	    [plugin] => <a href="http://www.viper007bond.com/wordpress-plugins/vipers-plugins-used/" title="Visit plugin homepage">Viper&#8217;s Plugins Used</a>
	    [author] => <a href="http://www.viper007bond.com/" title="Visit author homepage">Viper007Bond</a>
	    [filename] => vipers_pluginsused.php
	)

    etc.
)`

There is also this data array:

`$viperspluginsused->customdata;`

It's the same as `plugindata`, but if a custom description exists for the plugin, then that description is used.