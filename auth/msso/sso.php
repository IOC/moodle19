<?php

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

httpsrequired();

$req = required_param('req', PARAM_ALPHA);
$user = optional_param('user', false, PARAM_RAW);
$time = optional_param('time', false, PARAM_INT);
$hash = optional_param('hash', false, PARAM_ALPHANUM);

$msso = get_auth_plugin('msso');
$msso->process_request($req, $user, $time, $hash);
