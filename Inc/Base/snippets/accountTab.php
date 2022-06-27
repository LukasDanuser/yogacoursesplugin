<?php

add_filter('uwp_account_available_tabs', 'uwp_account_available_tabs_cb', 10, 1);
function uwp_account_available_tabs_cb($tabs)
{

    $tabs['courses'] = array(
        'title' => __('Meine Kurse', 'userswp'),
        'icon'  => 'fas fa-user',
    );

    return $tabs;
}

add_filter('uwp_account_page_title', 'uwp_account_page_title_cb', 10, 2);
function uwp_account_page_title_cb($title, $type)
{
    if ($type == 'courses') {
        $title = __('Meine Kurse', 'uwp-messaging');
    }

    return $title;
}

add_filter('uwp_account_form_display', 'uwp_account_form_display_cb', 10, 1);
function uwp_account_form_display_cb($type)
{
    global $wpdb;
    if ($type == 'courses') {
        $plugin_data = get_plugin_data(dirname(__FILE__, 3) . '/coursesplugin/courses-plugin.php');
        $plugin_version = $plugin_data['Version'];
        wp_enqueue_style('style', '/wp-content/plugins/coursesplugin/assets/style.css', __FILE__, $plugin_version);
        echo 'Sie sind für folgende Kurse angemeldet<br><br>';
        $registeredCourses = explode(";", $wpdb->get_var("SELECT registered_courses FROM $wpdb->prefix" . "users WHERE ID = " . get_current_user_id()));
        $courseAmount = count($registeredCourses);
        $rowsNeeded = ceil($courseAmount / 2);
        $t1 = 0;
        if ($courseAmount > 1) {
            $t1 = 1;
        } else {
            $t1 = 2;
        }
        foreach ($registeredCourses as $registeredCourse) {
            if ($registeredCourse == null or $registeredCourse == "" or $registeredCourse > 1) {
                continue;
            }
            $course = $wpdb->get_row("SELECT * FROM $wpdb->prefix" . "courses WHERE ID = " . $registeredCourse);
            $cDate = new DateTime($course->date);
            $courseDate = $cDate->format('d.m.Y H:i');
            echo "<div class=\"course\" style=\"grid-column: $t1; grid-row: $rowsNeeded; \">
            <div id=\"course$course->id\" class=\"course-card\">
        <div class=\"card-body\">
            <h5 class=\"card-title text-center\"><b><u>
                        <p>$course->course_name</p>
                    </u></b></h5>
            <p class=\"card-text\">$course->description</p>
            <p><b>Datum:</b> $courseDate</p>
        </div>
    </div></a>
</div>
            ";
        }
    }
}
