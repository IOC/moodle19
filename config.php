<?php

unset($CFG);

$CFG = new stdClass();
$CFG->dbpersist = false;
$CFG->admin = 'admin';
$CFG->skiplangupgrade = true;

date_default_timezone_set('Europe/Madrid');

require_once(dirname(__FILE__) . '/../config-moodle.php');

require_once("$CFG->dirroot/lib/setup.php");
