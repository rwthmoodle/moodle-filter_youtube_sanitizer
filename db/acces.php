<?php

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
