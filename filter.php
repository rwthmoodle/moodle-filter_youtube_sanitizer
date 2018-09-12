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
	/**
	* Get the DOMDocument from the context. Find all the iframes and replace them with divs.
	* Add the script to bind the click functionality to the div so the Video starts on click.
	*
	* @param string $text contains string
	* @param array $options array of window sizes for the video
	* @return  object DOMDoc object
	*/
    public function filter($text, array $options = array()) {
        // Create DOMDocument from the context.
        $dom = new DOMDocument;
        @$dom->loadHTML($text);
        // Get all the Iframe Elements from the DOMDocument.
        $nodes = $dom->getElementsByTagName('iframe');
        foreach ($nodes as $node) {
            // Get the Attributes of the node.
            $src = $node->getAttribute('src');
            $href = $node->getAttribute('href');
            $parent = $node->parentNode;

            // Get the right part of the node and replace it
            if(preg_match("=youtube.*embed/=i", $src)) {
				$src .= '&autoplay=1&controls=1';
                $newNode =    $this->video_embed_privacy_translate($node, $src);
                $node->parentNode->replaceChild($newNode, $node) ;
            }
        }
        //Return the changed HTML string
		$returndom = $dom-> saveHTML($dom);
        return $dom->saveHTML($dom);
    }

	/**
	* this function does place the JavaScript insende the head of the page und creates the DOMDoc nodes to be prcessed by the
	* filter function of this class
	*
	* @param object $node contains the DOMDoc object
	* @param string $url string conatining th e urk from the secific iframe
	* @return  string $newdiv that contaains the HTML for the embedded videos
	*/
    public function video_embed_privacy_translate($node, $url) {

        global $PAGE, $CFG;
        // Reqiure the nescassary JS-file that handles everything associated with clicks and touches.
        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/filter/youtube_sanitizer/video-embed-privacy.js'));
        $src = $node->getAttribute('src');
        $width = $node->getAttribute('width');
        $height = $node->getAttribute('height');
		$node->setAttribute('height', 300);
		$width = 300*1.7777;
		$node->setAttribute('width', $width);
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
		// setting all the needed  strings
        $urlg = get_string('url', 'filter_youtube_sanitizer');
        $nojstext = get_string('no-js-message', 'filter_youtube_sanitizer');
        $terms = get_string('terms', 'filter_youtube_sanitizer');
        $cond = get_string('conditions', 'filter_youtube_sanitizer');
        $playtext = '<div class="overlay">' . $button . '</div>';
		$playtext .= '<div class="yt-link-wrapper"><span class="small"> ' . $terms . '</span>';
        $playtext .= '<a class="small" href="' . $urlg . '" target="_blank"> ' . $cond . '</a><div>';
     //   	$preview = new moodle_url($CFG->wwwroot . "/filter/youtube_sanitizer/video-embed-privacy/preview/preview.php?");
        $newdiv = $node->ownerDocument->createElement('div');
        $newdiv->setAttribute('class', "video-wrapped");
        $newdiv->setAttribute('allow', "enctrypted-media;autoplay;");
        // $newdiv->setAttribute('style', "background-image: url($preview);background-position:center; background-repeat: no-repeat;");
		$newdiv->setAttribute('data-embed-play', $playtext);
        $newdiv->setAttribute('data-embed-frame', $node->ownerDocument->saveHTML($node));
        return $newdiv;
    }

	public function filter_youtube_sanitizer_before_standadrd_html_head() {
		$css = '<style>.video-wrapped,span.mediaplugin{height:calc((50% - 5px) * 0.5625)!important;';
		$css .=	'width:calc(50% - 5px)!important;}</style>';
		return $css;

	}

}
