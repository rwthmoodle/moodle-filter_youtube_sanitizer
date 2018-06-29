<?php
 // Automatic media embedding filter class extended for YT privacy
 //
 // It is highly recommended to configure servers to be compatible with our slasharguments,
 // otherwise the "?d=600x400" may not work.
 //
 // @package    filter
 // @subpackage mediaplugin
 // @copyright  2004 onwards Martin Dougiamas  {@link http://moodle.com}
 // @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later



class filter_youtube_sanitizer extends moodle_text_filter {



    public function filter($text, array $options = array()) {

      	/**
       	* Converts the $PAGE into DOMDocument object. Searches the DomObjects for nodes that contain either iframes and their
		* attributes.
       	* Gives the Node object to "video_embed_privacy_translate" and replaces the old node with the new one.
       	* @param $text contains the content to be filtered
       	* @param $options is an array that contains all the options one could give to to generated page
       	*/

        //Create DOMDocument from the context.
        $dom = new DOMDocument;
        $dom->loadHTML($text);
        //Get all the Iframe Elements from the DOMDocument
        foreach ($dom->getElementsByTagName('iframe') as $node) {
            //Get the Attributes of the node
            $src = $node->getAttribute('src');
            $href = $node->getAttribute('href');
            $parent = $node->parentNode;
            // Get the right part of the node and replace it
            if(preg_match("=youtube.*embed/=i", $src)) {
                $newNode =    $this->video_embed_privacy_translate($node, $src);
                $node->parentNode->replaceChild($newNode, $node);
            }
        }
        foreach ($dom->getElementsByTagName('a') as $nodeA) {
            $hrefA = $nodeA->getAttribute('href');
            if(preg_match("=youtube.*="/*embed/=i"*/, $hrefA)) {
                echo '<pre>';var_dump($hrefA);echo '</pre>';

            }
        }
        //Return the changed HTML string
        return $dom->saveHTML($dom);
    }

    public function video_embed_privacy_translate($node, $url) {

        /**
        * @param $node is the DOMDocument Object
        * @param $url is the URL of the Iframe
        */

        global $PAGE, $CFG;

        //reqiure the nescassary JS-file that handles everything associated with clicks and touches
        // $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/filter/youtube_sanitizer/video-embed-privacy/video-embed-privacy.js'));
        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/filter/youtube_sanitizer/video-embed-privacy.js'));
        $src = $node->getAttribute('src');
        $width = $node->getAttribute('width');
        $height = $node->getAttribute('height');
        $yt_btn = <<<EOT
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
        $URL = get_string('url', 'filter_youtube_sanitizer');
        $NO_JS_TEXT = get_string('no-js-message', 'filter_youtube_sanitizer');
        $TERMS = get_string('terms', 'filter_youtube_sanitizer');
        $COND = get_string('conditions', 'filter_youtube_sanitizer');
        $PLAY_TEXT = '<div class="overlay">' . $yt_btn . '<div class="small"> ' . $TERMS ;
        $PLAY_TEXT .= '<a href="' . $URL . '" target="_blank"> ' . $COND . '</a>';

        if (!preg_match("=youtube.*embed/([\\w-]+)=i", $url, $matches)) {
            return $url;
        }

        $v = $matches[1];
        $preview = new moodle_url($CFG->wwwroot . "/filter/youtube_sanitizer/video-embed-privacy/preview/preview.php?v=$v");
        $newDiv = $node->ownerDocument->createElement('div');
        $newDiv->setAttribute('class',"video-wrapped");
        $newDiv->setAttribute('allow', "enctrypted-media;autoplay;");
        $newDiv->setAttribute('style',"background-image: url($preview);background-position:center; background-repeat: no-repeat;");

        $newDiv->setAttribute('data-embed-frame',$node->ownerDocument->saveHTML($node));
        $newDiv->setAttribute('data-embed-play',$PLAY_TEXT);
        return $newDiv;
    }

}
