<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *  Media plugin filtering
 *
 *  This filter will replace any links to a media file with
 *  a media plugin that plays that media inline
 *
 * @package    filter_youtube_sanitizer
 * @subpackage mediaplugin
 * @copyright  2018 Markus Offermann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \filter_youtube_sanitizer\domnodelist_reverse_iterator;

/**
 * Filter Youtube Sanitizer.
 *
 * It is highly recommended to configure servers to be compatible with our slasharguments,
 * otherwise the "?d=600x400" may not work.
 *
 * @package    filter_youtube_sanitizer
 * @subpackage youtube-sanitizer
 * @copyright  2018 Markus Offermann  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_youtube_sanitizer extends moodle_text_filter {

    private static $jsadded = false;

    /**
     * Get the DOMDocument from the context. Find all the iframes and replace them with divs.
     * Add the script to bind the click functionality to the div so the Video starts on click.
     *
     * @param string $text contains string
     * @param array $options array of window sizes for the video
     * @return  object DOMDoc object
     */
    public function filter($text, array $options = array()) {
        // Return the Filter content directly if it doesnt contain any <iframe> !

        if (stripos($text, '</iframe>') === false) {
            // Performance shortcut - if there are no </video> tags, nothing can match.
            return $text;
        }

        // Temporarily disable html parse errors.
        $useerrors = libxml_use_internal_errors(true);

        // Create DOMDocument from the context.
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$text);

        libxml_use_internal_errors($useerrors);
        libxml_clear_errors();

        // Get all the Iframe Elements from the DOMDocument.
        $nodes = $dom->getElementsByTagName('iframe');
        foreach (new domnodelist_reverse_iterator($nodes) as $node) {
            // Get the Attributes of the node.
            $src = $node->getAttribute('src');
            $url = new moodle_url($src);
            list($videoid, $listid) = $this->parse_ids($url);
            if ($videoid == null && $listid == null) {
                // Not a supported YouTube video URL.
                continue;
            }
            // Get the Video Information by sending requests with the oembed parameter.
            $yturl = 'http://youtube.com/oembed?url=http%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D' . $videoid . '&format=json';
            if (self::get_http_response_code($yturl) !== "301") {
                $oembed = file_get_contents($yturl);
            } else {
                $oembed = '';
            }
            $videoinfo = json_decode($oembed, true);
            // Get the tumbnail from the Videoinformation !
            if (isset($videoinfo['thumbnail_url']) == null) {
                $thumbnail = '';
            } else {
                $thumbnail = file_get_contents($videoinfo['thumbnail_url']);
            }
            // Get the right part of the node and replace it !
            // Example URL for video series: https://www.youtube.com/embed/videoseries?list=SPwHMzH35WbRIBdLm5yYzi1LvayrqoGQo1.
            $url->param('autoplay', '1');
            $url->param('controls', '1');
            $newnode = $this->video_embed_privacy_translate($node, $src, $videoid, $listid);
            $parent = $node->parentNode;
            if ($parent && $parent->hasAttribute('class')
                && strpos($parent->getAttribute('class'), 'mediaplugin_youtube') !== false) {
                $node = $parent;
            }

            $node->parentNode->replaceChild($newnode, $node);
        }
        // Return the changed HTML string.
        $text = $dom->saveHTML();

        return $text;
    }

    /** Checks if the given URL is valid */
    public function get_http_response_code($url) {
        $headers = get_headers($url);
        return substr($headers[0], 9, 3);
    }
    /**
     * Extract video/list id from URL.
     *
     * @return array [videoid, listid] one or both may be null
     */
    private function parse_ids(moodle_url $url) {
        $videoid = $listid = null;
        $host = $url->get_host();
        $path = $url->get_path();
        $matches = [];
        switch ($host) {
            case 'youtu.be':
                if (preg_match('=^/([A-Za-z0-9_\\-]{11})$=', $path, $matches)) {
                    $videoid = $matches[1];
                }
                break;
            case 'www.youtube.com':
            case 'www.youtube-nocookies.com':
                if ($path == '/embed/videoseries') {
                    $listid = $url->get_param('list');
                } else if ($path == '/watch)') {
                    $videoid = $url->get_param('v');
                } else if (preg_match('=^/(?:v|e(?:mbed)?)/([A-Za-z0-9_\\-]{11})$=', $path, $matches)) {
                    $videoid = $matches[1];
                }
                break;
        }
        return [$videoid, $listid];
    }

    /**
     * this function does place the JavaScript insende the head of the page und creates the DOMDoc nodes to be prcessed by the
     * filter function of this class
     *
     * @param object $node contains the DOMDoc object
     * @param string $url string conatining th e urk from the secific iframe
     * @return  string $newdiv that contaains the HTML for the embedded videos
     */
    public function video_embed_privacy_translate($node, $url, $videoid, $listid) {

        global $PAGE, $CFG;
        // Reqiure the nescassary JS-file that handles everything associated with clicks and touches.
        if (!self::$jsadded ) {
            $PAGE->requires->js_call_amd("filter_youtube_sanitizer/video-embed-privacy", "init");
            self::$jsadded = true;
        }
        // Setting up the video Wrapper.
        $cache = cache::make('filter_youtube_sanitizer', 'youtubethumbnails');
        $node->setAttribute('src', $url);
        $yturl = 'https://youtube.com/oembed?url=https%3A%2F%2Fwww.youtube.com%2F';
        $yturl .= ($videoid === null ? 'playlist%3flist=' . $listid : 'watch%3Fv%3D' . $videoid) . '&format=json&autoplay=1';
        $oembed = @file_get_contents($yturl);
        $videoavailable = $oembed !== false;
        if ($videoavailable) {
            // Video is accessible.
            $videoinfo = json_decode($oembed, true);
            $thumbratio = $videoinfo['thumbnail_width'] / $videoinfo['thumbnail_height'];
            $videoratio = $videoinfo['width'] / $videoinfo['height'];
            $height = $videoinfo['height'];
            $node->setAttribute('height', $videoinfo['height']);
            $thumbwidth = intval($videoinfo['thumbnail_height'] * $thumbratio);
            $videowidth = intval($videoinfo['height'] * $videoratio);
            $videowidthstring = $videowidth . 'px';
            $videoheightstring = $height . 'px';
        } else {
            // Video is no longer accessible.
            $videowidthstring = '450px';
            $videoheightstring = '300px';
        }
        $node->setAttribute('style', "width:100%;max-width:$videowidthstring;min-width:300px;");
        $node->setAttribute('allow', 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture');
        $node->setAttribute('allowfullscreen', '1');
        $node->setAttribute('allowautoplay', '1');
        $node->setAttribute('class', 'youtube-video');
        // Adding the Play Button.
        $button = <<<EOT
            <svg class="privacy-play-btn" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
            version="1.1" id="YouTube_Icon" x="0px" y="0px" viewBox="0 0 1024 721" enable-background="new 0 0 1024 721"
            xml:space="preserve" ><path id="Triangle" fill="#FFFFFF" d="M407,493l276-143L407,206V493z"/><path id="The_Sharpness"
            opacity="0.12" fill="#420000" enable-background="new    " d="M407,206l242,161.6l34-17.6L407,206z"/>
            <g id="Lozenge" style="&#10;    width:  30px;&#10;    height:  0px;&#10;"><g><linearGradient id="SVGID_1_"
            gradientUnits="userSpaceOnUse" x1="512.5" y1="719.7" x2="512.5" y2="1.2" gradientTransform="matrix(1 0 0 -1 0 721)">
            <stop offset="0" style="stop-color:#E52D27"/><stop offset="1" style="stop-color:#BF171D"/></linearGradient>
            <path class="path-yt" fill-opacity="0.8"
            d="M1013,156.3c0,0-10-70.4-40.6-101.4C933.6,14.2,890,14,870.1,11.6C727.1,1.3,512.7,1.3,512.7,1.3h-0.4
            c0,0-214.4,0-357.4,10.3C135,14,91.4,14.2,52.6,54.9C22,85.9,12,156.3,12,156.3S1.8,238.9,1.8,321.6v77.5
            C1.8,481.8,12,564.4,12,564.4s10,70.4,40.6,101.4c38.9,40.7,89.9,39.4,112.6,43.7c81.7,7.8,347.3,10.3,347.3,10.3
            s214.6-0.3,357.6-10.7c20-2.4,63.5-2.6,102.3-43.3c30.6-31,40.6-101.4,40.6-101.4s10.2-82.7,10.2-165.3v-77.5
            C1023.2,238.9,1013,156.3,1013,156.3z M407,493l0-287l276,144L407,493z"/></g></g></svg>
EOT;
        // Setting all the needed strings.
        $urlg = get_string('url', 'filter_youtube_sanitizer');
        $nojstext = get_string('no-js-message', 'filter_youtube_sanitizer');
        $terms = get_string('terms', 'filter_youtube_sanitizer');
        $cond = get_string('conditions', 'filter_youtube_sanitizer');
        $playtext = '<div height="100%" width="100%" class="overlay">' . $button . '</div>';
        $playtext .= '<div class="yt-link-wrapper"><span class="small-yt-link"> ' . $terms . '</span>';
        $playtext .= '<a class="small-yt-link" href="' . $urlg . '" target="_blank"> ' . $cond . '</a><div>';

        $newdiv = $node->ownerDocument->createElement('div');
        $newdiv->setAttribute('class', "video-wrapped");
        if ($videoavailable) {
            // Generating the preview picture wrapper.
            $preview = $this->get_preview($videoid, $videoinfo, $cache);
            // Mamually adding header and building img raw Data for parsing into the img tags source.
            $preview = "data:image/jepg;base64," .  $preview;
            $newimg = $node->ownerDocument->createElement('img');
            $newimg->setAttribute('src', $preview);
            $newdiv->appendChild($newimg);
        }

        // Getting the video size from JSON and passing the values to the img tag.
        $newdiv->setAttribute('allow', "enctrypted-media;autoplay;");
        $stylesstring = "height:100%;width:100%;max-width:$videowidthstring;max-height:$videoheightstring;";
        $stylesstring .= "min-width:270px;margin:auto;display:block;";
        if ($videoavailable) {
            $stylesstring .= "background-image: url($preview);";
        } else {
            $stylesstring .= "background-color: black;";
        }
        $stylesstring .= "background-position:center; background-repeat: no-repeat; background-size: cover;";
        $newdiv->setAttribute('style', $stylesstring);
        $newdiv->setAttribute('data-embed-play', $playtext);
        $newdiv->setAttribute('data-embed-frame', $node->ownerDocument->saveHTML($node));
        return $newdiv;
    }


    public function get_preview($videoid, $videoinfo, $cache) {

        global $PAGE, $CFG;

        $imgurl = "https://img.youtube.com/vi/$videoid/0.jpg";
        $image = false;
        // Check if URL is valid.
        // Prepare Cache for the thumbnails.
        if (!isset($cache)) {
            $image = file_get_contents("https://img.youtube.com/vi/$videoid/0.jpg");
        }
        // Check if there is any stored VideoID in the cache to generate URL for the image.
        if ($cache->get($videoid) !== false) {
            $image = $cache->get($videoid);
        } else {
            if (self::get_http_response_code($imgurl) !== "404") {
                $image = base64_encode(file_get_contents($imgurl));
            }
        }
        if ($image == false) {
            // Make Image of the size of the wrapper for creating placholder image for missing files.
            $placeholder = imagecreatetruecolor($videoinfo['width'] , $videoinfo['height'] );
            $bg = imagecolorallocate($placeholder, 100, 100, 100);
            imagefilledrectangle($placeholder, 0, 0, $videoinfo['width'], $videoinfo['height'], $bg);
            $image = $placeholder;
            $tmp = tempnam( sys_get_temp_dir(), 'img' );
            imagepng($placeholder, $tmp);
            imagedestroy($image);
            $image = base64_encode(file_get_contents($tmp));
            @unlink($tmp);
        }
        if (isset($image)) {
            // If file downloaded successfully -> Caching the thumbnail in the Applicationcache.
            $cache->set($videoid, $image);
        } else {
            $errortext = $result;
        }
        return $image;
    }
}
