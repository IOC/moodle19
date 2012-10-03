<?php

require_once('../config.php');

header('Content-type: text/plain');

function print_overview_ajax($courses, $full=true) {

    global $CFG, $USER;

    fetch_overview($courses, $full, $cat_names, $cat_courses, $htmlarray);

    $htmloutput = '';

    foreach ($cat_names as $id => $name) {
        if (empty($cat_courses[$id])) continue;
        $htmloutput .= print_simple_box_start('','','','','categorybox','',true);
        $htmloutput .= print_heading($name,'',3,'main',true);
        foreach ($cat_courses[$id] as $course) {
            $show_overview = '';
            if ($course->visible) {
                if ($full) {
                    if (array_key_exists($course->id, $htmlarray)) {
                        if (count($htmlarray[$course->id]) > 0) {
                            $show_overview = '';
                            foreach (array_keys($htmlarray[$course->id]) as $mod) {
                                $modname = get_string("modulenameplural", $mod);
                                $show_overview .= '&nbsp;<a href="#"'
                                    . ' class="roverview-link" id="roverview-'
                                    . $course->id . '-' . $mod . '-link"'
                                    . ' title="' . $modname . '">'
                                    . '<img src="' . $CFG->modpixpath . '/' . $mod
                                    . '/icon.gif" class="icon" alt="' . $modname
                                    . '" /></a>';
                            }
                        }
                    }
                } else {
                    $show_overview = '<img class="overview-loading"'
                        . ' src="'. $CFG->pixpath . '/i/ajaxloader.gif"'
                        . ' style="display: none" alt="" />';
                }
            }

            $htmloutput .= print_simple_box_start('center', '100%', '', 5, "coursebox",'',true);
            $linkcss = '';
            if (empty($course->visible)) {
                $linkcss = 'class="dimmed"';
            }
            $htmloutput .= print_heading('<a title="'. format_string($course->fullname).'" '.$linkcss.' href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">'. format_string($course->fullname).'</a>&nbsp;'.$show_overview,'',3,'main',true);
            if (array_key_exists($course->id,$htmlarray)) {
                foreach ($htmlarray[$course->id] as $modname => $html) {
                    $htmloutput .= '<div class="rcourse-overview"  id="roverview-' . $course->id
                        . '-' . $modname .'">' . $html . '</div>';
                }
            }
            $htmloutput .= print_simple_box_end(true);
        }
        $htmloutput .= print_simple_box_end(true);
    }
    return $htmloutput;
}

if (!empty($USER->id)) {
    $callback = optional_param('callback', '', PARAM_TEXT);
    $courses = get_my_courses($USER->id, 'visible DESC,sortorder ASC', '*', false);
    foreach ($courses as $c){
        $c->lastaccess = 0;
    }
    if (!empty($callback)){
        echo $callback.'(';
    }
    echo json_encode(print_overview_ajax($courses,true));

    if (!empty($callback)){
        echo ');';
    }
}
