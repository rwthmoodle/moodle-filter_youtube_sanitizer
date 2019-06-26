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
 * Youtube Sanitizer
 *
 * @package    filter_youtube_sanitizer
 * @subpackage tidy
 * @copyright  Markus Offermann 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$capabilities = array(
	'filter/youtube_sanitizer:termsConditionURL' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSE,
		'archetypes' => array(
			'editingteacher' => CAP_ENABLED,
			'student' => CAP_PROHIBIT,
			'user' => CAP_PROHIBIT
		),
		'clonepermissionsfrom' = 'moodle/my:manageblocks'
	),

	'block/simplehtml:addinstance' => array(
	  'riskbitmask' => RISK_SPAM | RISK_XSS,

	  	'captype' => 'write',
	  	'contextlevel' => CONTEXT_BLOCK,
	  	'archetypes' => array(
		  	'editingteacher' => CAP_ALLOW,
		  	'manager' => CAP_ALLOW
	  	),

	  	'clonepermissionsfrom' => 'moodle/site:manageblocks'
  	),

);
