<?php

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_proposal_rate' => [
        'classname' => 'mod_proposal\external\rate',
        'classpath' => 'mod/proposal/classes/external/rate.php',
        'methodname' => 'rating',
        'description' => 'Rate a proposal.',
        'type' => 'write',
        'ajax' => true
    ],
];
