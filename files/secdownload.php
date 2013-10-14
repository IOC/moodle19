<?php

require_once('../config.php');

require_login();

$path = required_param('path', PARAM_PATH);
$path = trim($path, '/');
$parts = explode('/', $path);
while (count($parts) > 0) {
    if ($records = get_records('local_materials', 'path', implode('/', $parts))) {
        foreach ($records as $record) {
            $context = get_context_instance(CONTEXT_COURSE, $record->course);
            if (has_capability('moodle/course:view', $context)) {
                $time = sprintf("%08x", time());
                $token = md5("{$CFG->local_materials_secret_token}/$path$time");
                $url = "{$CFG->local_materials_secret_url}/$token/$time/$path";
                @header($_SERVER['SERVER_PROTOCOL'] . ' 302 Found');
                @header('Location: ' . $url);
                exit;
            }
        }
    }
    array_pop($parts);
}

if (isset($CFG->local_redirect_url) && !empty($CFG->local_redirect_url)) {
    @header('Location: ' . $CFG->local_redirect_url . 'local/materials/secdownload.php?path='.$path);
} else {
    print_error('coursenotaccessible');
}
