Filter Youtube Sanitizer
==================================

Documentation
-------------
This  plugin provides a way to embed Youtube Videos via the mediaplugin without compromising the Privacy of the site visitor, by sending data of his session to the Serverside.

Plugin installation
---------------------
You need to have a running moodle V3.* on your server  to install this plugin.

1. cd into your `moodle/filter/` folder
2. clone the Git Repository from https://github.com/eggSmellFart/moodle-filter_youtube_sanitizer.git
3. if you want to Download the plugin data manually just use this link https://github.com/eggSmellFart/moodle-filter_youtube_sanitizer/archive/master.zip and unpack the Data inside into your `moodle/filter` folder. The plugin should reside in `moodle/filter/youtube_sanitizer/`.
4. in the settings of your mediaplugin standard filter (Website-administration->plugins->filter->multimedia-plugins) you got to unmark the check boxes for Youtube. So the Multimedia-oplugins dont handle the Youtube Links anymore.

Windows support
---------------
* use `\` instead of `/` in paths in examples above
