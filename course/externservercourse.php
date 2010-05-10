<?php

function extern_server_course($course) {
    global $CFG, $USER;

    $secretaries = array(70 => array('fpo_tickets', 'fpo/fpo_index.php'),
                         71 => array('cpl_tickets', 'cpl/cpl_index.php'),
                         311 => array('ioc_tickets', 'ioc/orla/index.php'),
                         324 => array('ioc_tickets', 'ioc/ioc_index.php'),
                         340 => array('btx_tickets', 'btx/btx_index.php'),
                         346 => array('acf_tickets', 'acf/acf_index.php'),
                         2808 => array('idi_tickets', 'idi/idi_index.php'),
                         2861 => array('idi_tickets', 'idi/Curs_ang1A.php'),
                         2862 => array('idi_tickets', 'idi/Curs_ang1B.php'),
                         3854 => array('idi_tickets', 'idi/Curs_ang2A.php'),
                         4223 => array('idi_tickets', 'idi/Curs_ang2B.php'),
                         4228 => array('idi_tickets', 'idi/Curs_ang3A.php'),
                         4229 => array('idi_tickets', 'idi/Curs_ang3B.php'),
                         5462 => array('fpd_tickets', 'fpd/fpd_index.php'),
                         5463 => array('eso_tickets', 'eso/eso_index.php'),
                         5714 => array('aad_tickets', 'aad/aad_index.php'));

    if (!empty($CFG->local_secretaria_baseurl) and isset($secretaries[$course->id]) and $USER->id) {
        list($table, $url) = $secretaries[$course->id];
        $ticket = rand(1, 1000000);
        $sql = "INSERT INTO $table VALUES('{$USER->username}', '$ticket')";
        if (!execute_sql($sql, false)) {
            error("S'ha produït un error en accedir a la secretaria.");
        }
        return "{$CFG->local_secretaria_baseurl}/$url?username={$USER->username}&ticket=$ticket";
    }

    return false;
}
