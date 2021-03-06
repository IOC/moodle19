<?php  // $Id$

    // this is the 'my moodle' page

    require_once('../config.php');
    require_once($CFG->libdir.'/blocklib.php');
    require_once($CFG->dirroot.'/course/lib.php');
    require_once('pagelib.php');
    
    require_login();

    if (optional_param('overview', false, PARAM_BOOL)) {
        $courses = get_my_courses($USER->id, 'visible DESC,sortorder ASC', '*', false);
        $site = get_site();
        if (array_key_exists($site->id, $courses)) {
            unset($courses[$site->id]);
        }
        foreach ($courses as $c) {
            if (isset($USER->lastcourseaccess[$c->id])) {
                $courses[$c->id]->lastaccess = $USER->lastcourseaccess[$c->id];
            } else {
                $courses[$c->id]->lastaccess = 0;
            }
        }
        if (empty($courses)) {
            print_simple_box(get_string('nocourses','my'),'center');
        } else {
            print_overview($courses, true);
        }
        die;
    }

    $mymoodlestr = get_string('mymoodle','my');

    if (isguest()) {
        $wwwroot = $CFG->wwwroot.'/login/index.php';
        if (!empty($CFG->loginhttps)) {
            $wwwroot = str_replace('http:','https:', $wwwroot);
        }

        print_header($mymoodlestr);
        notice_yesno(get_string('noguest', 'my').'<br /><br />'.get_string('liketologin'),
                     $wwwroot, $CFG->wwwroot);
        print_footer();
        die();
    }

     // Bounds for block widths
    // more flexible for theme designers taken from theme config.php
    $lmin = (empty($THEME->block_l_min_width)) ? 100 : $THEME->block_l_min_width;
    $lmax = (empty($THEME->block_l_max_width)) ? 210 : $THEME->block_l_max_width;
    $rmin = (empty($THEME->block_r_min_width)) ? 100 : $THEME->block_r_min_width;
    $rmax = (empty($THEME->block_r_max_width)) ? 210 : $THEME->block_r_max_width;

    define('BLOCK_L_MIN_WIDTH', $lmin);
    define('BLOCK_L_MAX_WIDTH', $lmax);
    define('BLOCK_R_MIN_WIDTH', $rmin);
    define('BLOCK_R_MAX_WIDTH', $rmax);

    $edit        = optional_param('edit', -1, PARAM_BOOL);
    $blockaction = optional_param('blockaction', '', PARAM_ALPHA);

    $PAGE = page_create_instance($USER->id);

    $pageblocks = blocks_setup($PAGE,BLOCKS_PINNED_BOTH);

    if (($edit != -1) and $PAGE->user_allowed_editing()) {
        $USER->editing = $edit;
    }

    require_js(array('yui_yahoo', 'yui_event', 'yui_dom', 'yui_connection', 'yui_json'));

    $PAGE->print_header($mymoodlestr);

    echo "<script type=\"text/javascript\">//<![CDATA[\n";
    include $CFG->dirroot . '/my/index.js';
    echo "\n//]]></script>";

    echo '<table id="layout-table">';
    echo '<tr valign="top">';

    $lt = (empty($THEME->layouttable)) ? array('left', 'middle', 'right') : $THEME->layouttable;
    foreach ($lt as $column) {
        switch ($column) {
            case 'left':

    $blocks_preferred_width = bounded_number(BLOCK_L_MIN_WIDTH, blocks_preferred_width($pageblocks[BLOCK_POS_LEFT]), BLOCK_L_MAX_WIDTH);

    if(blocks_have_content($pageblocks, BLOCK_POS_LEFT) || $PAGE->user_is_editing()) {
        echo '<td style="vertical-align: top; width: '.$blocks_preferred_width.'px;" id="left-column">';
        print_container_start();
        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);
        print_container_end();
        echo '</td>';
    }
    
            break;
            case 'middle':
    
    echo '<td valign="top" id="middle-column">';
    print_container_start(TRUE);

/// The main overview in the middle of the page
    $courses_limit = 21;
    if (isset($CFG->mycoursesperpage)) {
        $courses_limit = $CFG->mycoursesperpage;
    }

    $morecourses = false;
    if ($courses_limit > 0) {
        $courses_limit = $courses_limit + 1;
    }

    $courses = get_my_courses($USER->id, 'visible DESC,sortorder ASC', '*', false);
    $site = get_site();
    $course = $site; //just in case we need the old global $course hack

    if (($courses_limit > 0) && (count($courses) >= $courses_limit)) {
        //remove the 'marker' course that we retrieve just to see if we have more than $courses_limit
        array_pop($courses);
        $morecourses = true;
    }

    if (array_key_exists($site->id,$courses)) {
        unset($courses[$site->id]);
    }

    foreach ($courses as $c) {
        if (isset($USER->lastcourseaccess[$c->id])) {
            $courses[$c->id]->lastaccess = $USER->lastcourseaccess[$c->id];
        } else {
            $courses[$c->id]->lastaccess = 0;
        }
    }

    if (!empty($CFG->local_myremotehost)) {
        if (!empty($CFG->local_myremote_message)) {
            echo '<div class="myremote-message myhidden">';
            echo format_text($CFG->local_myremote_message, FORMAT_HTML);
            echo '</div>';
        }
    }

    if (empty($courses)) {
        print_simple_box(get_string('nocourses','my'),'center');
    } else {
        echo '<div id="course-list">';
        print_overview($courses, false);
        echo '</div>';
    }
    
    $all_courses = get_my_courses($USER->id, 'visible DESC,sortorder ASC',
                                  null, false, 0, false);
    $other_courses = array();
    foreach ($all_courses as $course) {
        if (!isset($courses[$course->id])) {
            $other_courses[$course->id] = $course;
        }
    }
    if ($other_courses) {
        $cat_names = array();
        $cat_parents = array();
        make_categories_list($cat_names, $cat_parents);
        $options = array();
        foreach ($other_courses as $course) {
            $category_name = $cat_names[$course->category];
            $options[$category_name][$course->id] = $course->fullname;
        };
        echo '<br/><form action="' . $CFG->wwwroot . '/course/view.php" method="get">';
        choose_from_menu_nested($options, 'id', '',
                                get_string('othercourses', 'local'),
                                'this.form.submit()', SITEID);
        echo '</form>';
    }

    echo '<br/><a href="' . $CFG->wwwroot . '/course/index.php">' . get_string('fulllistofcourses') . ' ...</a>';

    // Remote courses on my Moodle
    if (!empty($CFG->local_myremotehost)) {
        print_remote_courses();
    }

    print_container_end();
    echo '</td>';
    
            break;
            case 'right':
            
    $blocks_preferred_width = bounded_number(BLOCK_R_MIN_WIDTH, blocks_preferred_width($pageblocks[BLOCK_POS_RIGHT]), BLOCK_R_MAX_WIDTH);

    if (blocks_have_content($pageblocks, BLOCK_POS_RIGHT) || $PAGE->user_is_editing()) {
        echo '<td style="vertical-align: top; width: '.$blocks_preferred_width.'px;" id="right-column">';
        print_container_start();
        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_RIGHT);
        print_container_end();
        echo '</td>';
    }
            break;
        }
    }

    /// Finish the page
    echo '</tr></table>';

    print_footer();

?>
