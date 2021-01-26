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

use filter_youtube_sanitizer\event\youtube_request_failed;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');

class filter_youtube_sanitizer extends moodle_text_filter {

    const MAX_WIDTH = 500;
    const MAX_HEIGHT = 300;
    private static $jsadded = false;

    public function filter($text, array $options = []) {
        // Return the Filter content directly if it doesnt contain any <iframe> !

        if (stripos($text, '<iframe') === false) {
            // Performance shortcut - if there are no <video> tags, nothing can match.
            return $text;
        }

        $cache = cache::make('filter_youtube_sanitizer', 'videoinfo');
        $text = preg_replace_callback('~(<span class="mediaplugin mediaplugin_youtube">[^<]*)?<iframe[^>]* src="((:?https?://)?(:?(:?www|m)\\.)?(:?youtube\\.com|youtube-nocookies\\.com|youtu\\.be)[^"]*)"[^>]*>[^<]*</iframe>(?(1)[^<]*</span>)~', function($matches) use ($cache) {
            $videourl = $matches[2];
            return $this->get_video_html($videourl, $cache);
        }, $text);

        return $text;
    }

    private function get_video_html(string $videourl, $cache) {
        global $OUTPUT, $PAGE;
        if (!self::$jsadded ) {
            $PAGE->requires->js_call_amd("filter_youtube_sanitizer/video-embed-privacy", "init");
            self::$jsadded = true;
        }

        $videoinfo = $this->get_videoinfo($videourl, $cache);

        if ($videoinfo->thumbnailmimetype) {
            $mimetype = $videoinfo->thumbnailmimetype;
            $thumbnail = $videoinfo->thumbnail;
            $style = 'background-image: url(data:'.$mimetype.';base64,'.$thumbnail.');';
            $style .= ' background-position:center; background-repeat: no-repeat; background-size: cover';
        } else {
            $style = 'background-color: black;';
        }

        $data = new stdClass();
        $data->style = $style;
        $data->iframe = $videoinfo->html;
        $data->error = $videoinfo->error;
        $data->paddingtop = (100 / $videoinfo->aspectratio);
        $data->maxwidth = self::MAX_HEIGHT * $videoinfo->aspectratio;
        return $OUTPUT->render_from_template('filter_youtube_sanitizer/player', $data);
    }

    public function get_videoinfo(string $videourl, $cache) {
        $key = $videourl;
        $videoinfo = $cache->get($key);
        if ($videoinfo === false) {

            $videoinfo = new stdClass();
            $videoinfo->thumbnail = false;
            $videoinfo->thumbnailmimetype = false;
            $videoinfo->aspectratio = 16 / 9;
            $url = new moodle_url($videourl);
            $url->param('autoplay', '1');
            $videoinfo->html = '<iframe width="100%" height="100%" src="'.$url->out().'"></iframe>';
            $videoinfo->error = '';

            $oembed = $this->get_oembed_info($videourl);
            if ($oembed === false) {
                $videoinfo->error = get_string('oembederror', 'filter_youtube_sanitizer');
                return $videoinfo;
            }
            $videoinfo->html = $oembed->html;
            $videoinfo->aspectratio = $oembed->width / $oembed->height;

            // Replace tiny width/height that oembed returns with 100%.
            $videoinfo->html = preg_replace('~(width|height)="[^"]*"~', '$1="100%"', $videoinfo->html);

            // Add autoplay to url.
            $videoinfo->html = preg_replace('~src="([^"?]+)"~', 'src="$1?autoplay=1"', $videoinfo->html);
            $videoinfo->html = preg_replace('~src="([^"]+\\?[^"]+)"~', 'src="$1&autoplay=1"', $videoinfo->html);

            $thumbnailurl = $oembed->thumbnail_url;
            $curlinfo = [];
            $response = $this->yt_get($thumbnailurl, $curlinfo);
            if ($response === false) {
                $videoinfo->error = get_string('thumbnailerror', 'filter_youtube_sanitizer');
                return $videoinfo;
            }
            $videoinfo->thumbnail = base64_encode($response);
            $videoinfo->thumbnailmimetype = $curlinfo['content_type'];

            $cache->set($key, $videoinfo);
        }
        return $videoinfo;
    }

    private function get_oembed_info(string $videourl) {
        $videourl = $this->fix_url_for_oembed($videourl);
        $oembedurl = new moodle_url('https://youtube.com/oembed', [
            'url' => $videourl,
            'format' => 'json',
            'maxwidth' => self::MAX_WIDTH,
            'maxheight' => self::MAX_HEIGHT
        ]);
        $oembedurl = $oembedurl->out(false);
        $response = $this->yt_get($oembedurl);
        if ($response === false) {
            return false;
        }
        return json_decode($response);
    }

    private function yt_get(string $url, array& $curlinfo = null) {
        if ($curlinfo === null) {
            $curlinfo = [];
        }

        $curl = new curl();
        $curlopts = [
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_FOLLOWLOCATION' => 1
        ];
        $response = $curl->get($url, [], $curlopts);
        $errno = $curl->get_errno();
        $curlinfo = $curl->get_info();
        if ($errno || $curlinfo['http_code'] >= 400) {
            $eventdata = [
                'context' => \context_system::instance(),
                'other' => [
                    'requesturl' => $url,
                    'curl_errno' => $errno,
                    'http_code' => $curlinfo['http_code'],
                    'http_response' => $response
                ]
            ];
            youtube_request_failed::create($eventdata)->trigger();
            return false;
        }
        return $response;
    }

    /**
     * Some URLs don't work with oembed. So we need to rewrite them.
     */
    private function fix_url_for_oembed(string $videourl) {
        $url = new moodle_url($videourl);
        $path = $url->get_path();
        $params = $url->params();
        if (preg_match('~^/(?:v|e(?:mbed)?)/(?!videoseries)([A-Za-z0-9_\\-]{11})$~', $path, $matches)) {
            $videoid = $matches[1];
            $url = new moodle_url('https://www.youtube.com/watch', $params);
            $url->param('v', $videoid);
        }
        return $url->out(false);
    }
}
