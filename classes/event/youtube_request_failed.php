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

namespace filter_youtube_sanitizer\event;

defined('MOODLE_INTERNAL') || die;

class youtube_request_failed extends \core\event\base {

    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name() {
        return get_string('oembederror', 'filter_youtube_sanitizer');
    }

    public function get_description() {
        return "The request for {$this->other['requesturl']} failed with curl errno {$this->other['curl_errno']},"
            ." http code {$this->other['http_code']} and response \"{$this->other['http_response']}\".";
    }

    public function get_url() {
        global $PAGE;
        return $PAGE->url;
    }
}

