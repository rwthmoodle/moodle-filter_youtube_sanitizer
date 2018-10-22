Filter Youtube Sanitizer
==================================

Documentation
-------------
This  plugin provides a way to embed Youtube Videos via the mediaplugin without compromising the Privacy of the site visitor, so no informaiton about the current user is sent to provider of the web service.  

This filter is applied after the moodle-native mediaplugin has done it's filtering thing with all the encountered Video Ressources. The Youtube-Sanitizer expects all the Video Tags to be converted to iframes (You need to changes a setting in the mediPlugin options as explained in #4 of the Installation instructions). Then it simply takes theses Iframes and builds a dummy player (grey box with YouTube play Logo), that contains the iframe, that previously was cut from the DOM in a dataAttribute. So when you click it gets replaced by the real iframe. That sadly means no preview images today, but no data is sent to YouTube by just visiting a side to display these images.  

Plugin installation
---------------------
You need to have a running moodle V3.* on your server  to install this plugin.

1. cd into your `moodle/filter/` folder
2. clone the Git Repository from https://www.url.org/some_repository
3. if you want to Download the plugin data manually just use this link https://www.url.org/some_repository/archive/master.zip and unpack the Data inside into your `moodle/filter` folder. The plugin should reside in `moodle/filter/youtube_sanitizer/`.
4. in the settings of your mediaplugin standard filter (Website-administration->plugins->filter->multimedia-plugins) you got to unmark the check boxes for Youtube. like explained above its nescasssary so the YouTubeSanatizer gets the data it expects. 
5. Last but not leat you have to configure the Multimedia-stadard thats included in Moodle, so it doesnt handle the Youtube Links.
To do that you have to navigate to the Plugin-settings page. There you choose your Multimediaplugin and uncheck the box that reads Youtube (as standard the box is checked).

Windows support
---------------
* use `\` instead of `/` in paths in examples above
