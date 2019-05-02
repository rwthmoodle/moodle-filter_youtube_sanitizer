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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

require_once($CFG->dirroot.'/lib/filelib.php');

$videoid = optional_param('vid', null, PARAM_TEXT);
$listid = optional_param('lid', null, PARAM_TEXT);

$c = new curl();
$file = fopen('savepath', 'w');
$result = $c->download_one("https://img.youtube.com/vi/$videoid/0.jpg", null,
  array('file' => $file, 'timeout' => 5, 'followlocation' => true, 'maxredirs' => 3));
fclose($file);
$download_info = $c->get_info();
if ($result === true) {
  // file downloaded successfully
} else {
  $error_text = $result;
  $error_code = $c->get_errno();
}

readfile('savepath');