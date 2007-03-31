<?php

/* 
Plugin Name: Viper's Plugins Used
Plugin URI: http://www.viper007bond.com/wordpress-plugins/vipers-plugins-used/
Version: 1.12
Description: Allows you to display alphabetically what plugins you have enabled on your blog in either a table or unordered list. Also allows you to set custom descriptions for the plugins in the output.
Author: Viper007Bond
Author URI: http://www.viper007bond.com/

--------------------------------------------------

If you use any of this code, please credit me (Viper007Bond) for my work. :)
I worked hard on this plugin and don't want others taking credit for my work.

*/ 

// Okay, now before we actually do anything, we need to handle any POSTs from the options page
// This is so that all changes are made before the class runs and gets the data

// Some common items that need to be stripped of slashes
$editpluginname = stripslashes($_POST['editpluginname']);
$editplugindescription = stripslashes($_POST['editplugindescription']);

// If saving the plugin description
if ($_POST['editpluginsave'] && $editpluginname) {
	$viperspluginsused = new viperspluginsused();
	$vipercustominfo = get_option('pluginsused_custtext');

	// Make sure we're editing an existing plugin
	if (!$viperspluginsused->plugindata[$editpluginname]) $savemessage = "&quot;<em>" . $editpluginname . "</em>&quot; " . __("is not a plugin that exists or that is activated. Therefore unable to save the description for it.");
	else {
		// Unset it if the saved description is exactly the same as the default
		if ($editplugindescription === $viperspluginsused->plugindata[$editpluginname]['description']) {
			unset($vipercustominfo[$editpluginname]);
			update_option('pluginsused_custtext', $vipercustominfo);
		}

		// Otherwise save the custom description
		else {
			$vipercustominfo[$editpluginname] = $editplugindescription;
			update_option('pluginsused_custtext', $vipercustominfo);
		}

		$savemessage =  __('Description for') . " &quot;<em>" . $editpluginname . "</em>&quot; " . __('saved') . ".";
	}
}
// ElseIf reseting the plugin description
elseif ($_POST['editpluginreset'] && $editpluginname) {
	$vipercustominfo = get_option('pluginsused_custtext');
	unset($vipercustominfo[$editpluginname]);
	update_option('pluginsused_custtext', $vipercustominfo);
	$resetsinglemessage = __('Description for') . " &quot;<em>" . $editpluginname . "</em>&quot; " . __('reset to default') . ".";
}
// ElseIf we're mass reseting
else if ($_POST['resetplugins']) {
	$vipercustominfo = get_option('pluginsused_custtext');
	$count = count($_POST['resetplugins']);
	$resetmultimessage = __('Descriptions for') . " &quot;<em>" . $_POST['resetplugins'][0] . "</em>&quot;";
	foreach ($_POST['resetplugins'] as $key => $plugin_name) {
		$plugin_name = stripslashes($plugin_name);
		unset($vipercustominfo[$plugin_name]);
		if ($key == 0) continue; // Skip the first one as we echoed it already
		$resetmultimessage .= ", ";
		if ($key + 1 == $count) $resetmultimessage .= "and "; // If we're on the last one, echo "and"
		$resetmultimessage .= "&quot;<em>$plugin_name</em>&quot;";
	}
	update_option('pluginsused_custtext', $vipercustominfo);
	$resetmultimessage .= " " . __('reset to default') . ".";
}

// Okay, now on to the plugin class that generates all the data
class viperspluginsused {

	var $plugindata;
	var $customdata;

	// This function creates an array with details about all active plugins
	// It's in a seperate function so that it only runs once
	function viperspluginsused() {
		$plugins = get_option('active_plugins');

		$output = array();

		// If you have like a URL click plugin or whatnot
		// $urlprefix = 'go.php?';

		foreach($plugins as $key => $filename) {

			if (!file_exists(ABSPATH . '/wp-content/plugins/' . $filename)) continue;
			if (!is_readable(ABSPATH . '/wp-content/plugins/' . $filename)) continue;
			
			$plugin_data = implode('', file(ABSPATH . '/wp-content/plugins/' . $filename));
			
			preg_match("|Plugin Name:(.*)|i", $plugin_data, $plugin_name);
			if ('' == $plugin_name[1]) continue;
			
			preg_match("|Plugin URI:(.*)|i", $plugin_data, $plugin_uri);
			preg_match("|Description:(.*)|i", $plugin_data, $description);
			preg_match("|Author:(.*)|i", $plugin_data, $author_name);
			preg_match("|Author URI:(.*)|i", $plugin_data, $author_uri);
			
			if (preg_match("|Version:(.*)|i", $plugin_data, $version)) $version = trim($version[1]);
			else $version = '';

			$plugin_name = trim($plugin_name[1]);
			$plugin_uri = trim($plugin_uri[1]);
			$description = trim($description[1]);
			$author_name = trim($author_name[1]);
			$author_uri = trim($author_uri[1]);

			$plugin = (substr($plugin_uri, 0, 4) != 'http') ? $plugin_name : '<a href="' . $urlprefix . $plugin_uri . '" title="' . __('Visit plugin homepage') . '">' . $plugin_name . '</a>';
			
			if (!$author_name) $author = '&nbsp;';
			else $author = (substr($author_uri, 0, 4) != 'http') ? $author_name : '<a href="' . $urlprefix . $author_uri . '" title="' . __('Visit author homepage') . '">' . $author_name . '</a>';

			$output[$plugin_name] = array(
				'plugin_uri' => $plugin_uri,
				'version' => $version,
				'author_name' => $author_name,
				'author_uri' => $author_uri,
				'description' => $description,

				// These next two are the ones in <a href="URL">Name</a> format
				'plugin' => $plugin,
				'author' => $author,

				// And finally the partial plugin path (usually just the plugin name) for if we need it
				'filename' => $filename
			);
		}

		ksort($output);

		$this->plugindata = $output;

		// And now we make the customized array
		if ($customdata = get_option('pluginsused_custtext')) {
			foreach ($customdata as $plugin_name => $description) {
				if ($this->plugindata[$plugin_name])
					$output[$plugin_name]['description'] = $description;
			}
		}

		$this->customdata = $output;
	}

	// Takes the plugin array and outputs it as a table
	function output_table($tabledetails = '', $displaydescription = TRUE, $displayversion = TRUE, $diplayauthor = TRUE) {
		// Set a default table style for when it's not specified
		if ($tabledetails == '') $tabledetails = 'width="100%" border="1" cellpadding="3" cellspacing="3"';

		echo '<table ' . $tabledetails . ">\n	<tr class='pluginheader'>\n		<th class='plugincolumn'>" . __('Plugin') . "</th>\n";
		if ($displayversion == TRUE) echo "		<th class='versioncolumn'>" . __('Version') . "</th>\n";
		if ($diplayauthor == TRUE) echo "		<th class='authorcolumn'>" . __('Author') . "</th>\n";
		if ($displaydescription == TRUE) echo "		<th class='descriptioncolumn'>" . __('Description') . "</th>\n";
		echo "	</tr>\n";

		foreach($this->customdata as $plugin_name => $plugin_details) {
			$rowstyle = ($rowstyle == 'class="pluginrow"') ? 'class="pluginrowalt"' : 'class="pluginrow"';

			echo "	<tr $rowstyle>\n";
			echo "		<td>" . wptexturize($plugin_details['plugin']) . "</td>\n";

			if ($displayversion == TRUE) {
				echo "		<td>";
				echo ($plugin_details['version']) ? wptexturize($plugin_details['version']) : '&nbsp;';
				echo "</td>\n";
			}

			if ($diplayauthor == TRUE) {
				echo "		<td>";
				echo ($plugin_details['author']) ? wptexturize($plugin_details['author']) : '&nbsp;';
				echo "</td>\n";
			}

			if ($displaydescription == TRUE) {
				echo "		<td>";
				echo ($plugin_details['description']) ? wptexturize($plugin_details['description']) : '&nbsp;';
				echo "</td>\n";
			}

			echo "	</tr>\n";
		}

		echo "</table>\n";
	}

	// Takes the plugin array and outputs it as an unordered list
	function output_list($displayuls = TRUE, $displayversion = TRUE, $displayauthor = TRUE, $displaydescription = FALSE) {
		if ($displayuls == TRUE) echo "<ul>\n";

		foreach($this->customdata as $plugin_name => $plugin_details) {
			echo "<li>" . wptexturize($plugin_details['plugin']);
			if ($displayversion == TRUE && $plugin_details['version']) echo ' v' . wptexturize($plugin_details['version']);
			if ($displayauthor == TRUE && $plugin_details['author']) echo ' by ' . wptexturize($plugin_details['author']);
			if ($displaydescription == TRUE && $plugin_details['description']) echo ' &#8212; ' . wptexturize($plugin_details['description']);
			echo "</li>\n";
		}

		if ($displayuls == TRUE) echo "</ul>\n";
	}

	// Returns the total number of plugins that are currently activated and located in the plugins folder
	function plugincount() {
		return count($this->plugindata);
	}
}

// The options page output for the plugin
function viperspluginsused_showoptionspage() {
	global $_POST, $savemessage, $resetsinglemessage, $resetmultimessage;

	// Even though we may have done this already at the top of this file,
	// we need to do it again 'cause if it was loaded before,
	// chances are the data has changed due to the POST
	$viperspluginsused = new viperspluginsused();
	$vipercustominfo = get_option('pluginsused_custtext');

	// If we saved a custom description
	if ($_POST['editpluginsave'] && $editpluginname)
		echo "<div class='updated'><p><strong>$savemessage</strong></p></div>\n\n";
	// ElseIf a description was reset to default from the single edit page
	elseif ($_POST['editpluginreset'] && $editpluginname)
		echo "<div class='updated'><p><strong>$resetsinglemessage</strong></p></div>\n\n";
	// ElseIf doing a multiple reset
	elseif ($_POST['resetplugins'])
		echo "<div class='updated'><p><strong>$resetmultimessage</strong></p></div>\n\n";
	
	echo "<div class='wrap'>\n";
	echo "	<h2>" . __("Viper's Plugins Used") . "</h2>\n";

	// This'll be set if we're editing a plugin
	$editplugin = stripslashes($_GET['editplugin']);

	// Now we see if we're editing a plugin
	if ($editplugin) {
		$currentdescription = ($vipercustominfo[$editplugin]) ? $vipercustominfo[$editplugin] : $viperspluginsused->plugindata[$editplugin]['description'];

		// Check to see that we're actually editing a valid plugin
		if ($viperspluginsused->plugindata[$editplugin]) {
			echo "	<p>" . __('Now editing the description for') . " &quot;<em>" . $editplugin . "</em>&quot;. " . __('You may use HTML if you wish.') . "</p>\n\n";
			echo "	<form name='editplugin' action='options-general.php?page=" . basename(__FILE__) . "' method='post'>\n";
			echo "	<input type='hidden' name='editpluginname' value='" . htmlspecialchars($editplugin, ENT_QUOTES) . "' />\n\n";
			echo "	<table width='100%' cellspacing='2' cellpadding='5' class='editform'>\n"; 
			if ($vipercustominfo[$editplugin]) {
				echo "		<tr valign='top'>\n";
				echo "			<th width='33%' scope='row'>" . __('Default') . ":</th>\n";
				echo "			<td>" . htmlspecialchars($viperspluginsused->plugindata[$editplugin]['description'], ENT_QUOTES) . "</td>\n";
				echo "		</tr>\n";
			}
			echo "		<tr valign='top'>\n";
			echo "			<th width='33%' scope='row'>" . __('Description') . ":</th>\n";
			echo "			<td><textarea name='editplugindescription' style='width: 500px;' cols='40' rows='10'>" . htmlspecialchars($currentdescription, ENT_QUOTES) . "</textarea></td>\n";
			echo "		</tr>\n";
			echo "	</table>\n\n";
			echo "	<p class='submit' style='text-align: center;'>\n";
			echo "		<input type='submit' name='editplugincancel' value='&laquo; " . __('Back') . "' />&nbsp;\n";
			if ($vipercustominfo[$editplugin]) echo "		<input type='submit' name='editpluginreset' value='" . __('Reset to Default') . "' />&nbsp;\n";
			echo "		<input type='submit' name='editpluginsave' value='" . __('Save') . " &raquo;' />\n";
			echo "	</p>\n";
			echo "	</form>\n";
		} else {
			echo "	<p>" . __('Sorry, no plugin called') . " &quot;<em>$editplugin</em>&quot; " . __("found! If you're sure that the plugin exists, <a href='plugins.php'>check here</a> to make sure that the plugin is still active.") . "</p>\n\n";
			echo "	<p><a href='options-general.php?page=" . basename(__FILE__) . "'>&laquo; " . __('Go Back') . "</a></p>\n";
		}
	} else { // Okay, we're not editing a plugin, so list them all out

?>

	<script type="text/javascript">
	<!--
	function checkAll(form)
	{
		for (i = 0, n = form.elements.length; i < n; i++) {
			if(form.elements[i].type == "checkbox") {
				if(form.elements[i].checked == true)
					form.elements[i].checked = false;
				else
					form.elements[i].checked = true;
			}
		}
	}
	//-->
	</script>

	<p><?php _e("Here you can change the descriptions for the plugins that you have activated. Note that any changes you make don't actually edit the plugin file itself, only what will display when you use one of the &quot;plugins used&quot; functions created by this plugin."); ?></p>

	<p><?php _e("Any plugin for which you have a custom description will be highlighted in color."); ?></p>

	<form name="manageplugins" id="manageplugins" action="" method="post"> 

	<table width="100%" cellpadding="3" cellspacing="3">
		<tr>
			<th scope="col">*</th>
			<th scope="col"><?php _e('Plugin'); ?></th>
			<th scope="col"><?php _e('Version'); ?></th>
			<th scope="col"><?php _e('Author'); ?></th>
			<th scope="col"><?php _e('Description'); ?></th>
			<th scope="col" width="40"><?php _e('Edit'); ?></th>
		</tr>
<?php

	$alternaterow = TRUE;

	// Now we list out each plugin
	foreach ($viperspluginsused->customdata as $plugin_name => $plugin_details) {
		echo '		<tr class="';
		// This is to make alternating row colors
		if ($alternaterow === TRUE) {
			echo ' alternate';
			$alternaterow = FALSE;
		} else {
			$alternaterow = TRUE;
		}
		if ($plugin_details['description'] !== $viperspluginsused->plugindata[$plugin_name]['description']) echo ' active';
		echo "\">\n";

		echo "			<td><input type='checkbox' name='resetplugins[]' value='" . htmlspecialchars($plugin_name, ENT_QUOTES) . "' /></td>\n";

		echo "			<td>" . wptexturize($plugin_details['plugin']) . "</td>\n";

		echo "			<td>";
		echo ($plugin_details['version']) ? wptexturize($plugin_details['version']) : '&nbsp;';
		echo "</td>\n";

		echo "			<td>";
		echo ($plugin_details['author']) ? wptexturize($plugin_details['author']) : '&nbsp;';
		echo "</td>\n";

		echo "			<td>";
		echo ($plugin_details['description']) ? wptexturize($plugin_details['description']) : '&nbsp;';
		echo "</td>\n";

		echo "			<td style='text-align: center;'><a href='" . htmlspecialchars(add_query_arg('editplugin', urlencode($plugin_name))) . "' title='" . __('Edit the details of this comment for display') . "'>Edit</a></td>\n";

		echo "		</tr>\n";
	}

?>
	</table>

	<p><a href="javascript:;" onclick="checkAll(document.getElementById('manageplugins')); return false; "><?php _e('Invert Checkbox Selection'); ?></a></p>
	
	<p class="submit"><input type="submit" name="submit" value="<?php _e('Reset Checked Plugins'); ?> &raquo;" title="<?php _e('Resets all selected plugins to their default description'); ?>" onclick="return confirm('<?php _e("You are about to reset the description of all checked plugins! \n  \'OK\' to reset, \'Cancel\' to stop."); ?>')" /></p>


	</form>
<?php } // End if $_GET['editplugin'] ?>
</div>

<?php 

} // End of the options page

// Function to add the options page to WordPress
function viperspluginsused_addoptionspage() {
	if (function_exists('add_options_page'))
		add_options_page(__("Viper's Plugins Used"), __("Plugins Used"), 8, basename(__FILE__), 'viperspluginsused_showoptionspage');
}

// Hook into WordPress and tell it about the above function
add_action('admin_menu', 'viperspluginsused_addoptionspage');

?>