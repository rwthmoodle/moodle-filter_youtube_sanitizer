Filter Youtube Sanitizer
==================================

Documentation
-------------
This  plugin provides a way to embed Youtube Videos via the mediaplugin without compromising the Privacy of the site visitor, by sending data of his session to third party servers.

Plugin installation
---------------------
You need to have a running moodle V3.* on your server  to install this plugin.

1. cd into your `moodle/filter/` folder
2. clone the Git Repository from https://www.url.org/some_repository
3. if you want to Download the plugin data manually just use this link https://www.url.org/some_repository/archive/master.zip and unpack the Data inside into your `moodle/filter` folder. The plugin should reside in `moodle/filter/youtube_sanitizer/`.
4. in the settings of your mediaplugin standard filter (Website-administration->plugins->filter->multimedia-plugins) you got to unmark the check boxes for Youtube. So the Multimedia-oplugins dont handle the Youtube Links anymore.
5. Last but not leat you have to configure the Multimedia-stadard thats included in Moodle, so it doesnt handle the Youtube Links.
To do that you have to navigate to the Plugin-settings page. There you choose your Multimediaplugin and uncheck the box that reads Youtube (as standard the box is checked).

Windows support
---------------
* use `\` instead of `/` in paths in examples above
