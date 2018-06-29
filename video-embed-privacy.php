<?php
/*
Plugin Name: Embed videos and respect privacy
Plugin URI:  https://wordpress.org/plugins/video-embed-privacy/
Description: Allows you to embed youtube videos without sending data to google on every page view.
Version:     1.1
Author:      Michael Zangl
License:     GPL2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: video-embed-privacy

This plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

This plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this plugin. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function video_embed_privacy_translate($iframe, $url, $atts) {
	// wp_enqueue_script('video-embed-privacy');
	//
	// $NO_JS_TEXT = 'Aktivieren Sie JavaScript um das Video zu sehen.<br/><a href="' . htmlspecialchars($url) . '">' . $url . '</a>';
	// $PLAY_TEXT = 'Abspielen<div class="small">Das Video wird von Youtube eingebettet abespielt. Es gilt die <a href="https://www.google.com/intl/de/policies/privacy/" target="_blank">Datenschutzerklärung von Google</a></div>';
	//
	// if (!preg_match("=youtube.*embed/([\\w-]+)=i", $iframe, $matches)) {
	// 	return $iframe;
	// }
	// $v = $matches[1];
	//
	// // plugin_dir_path( __FILE__ )
	// $preview = plugins_url("preview/$v.jpg", __FILE__);
	// return '<div class="video-wrapped" style="background-image: url(\'' .  $preview .  '\')" data-embed-frame="' . htmlspecialchars($iframe) . '" data-embed-play="' . htmlspecialchars($PLAY_TEXT) . '"><div class="video-wrapped-nojs">' . $NO_JS_TEXT . '</div></div>';

}
