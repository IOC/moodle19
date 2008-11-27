<?php

require_once('config.php');

if (get_record('user', 'id', 1)) {
    echo 'OK';
}
