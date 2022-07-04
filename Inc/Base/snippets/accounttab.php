<?php
add_filter('uwp_account_available_tabs', 'uwp_account_available_tabs_cb', 10, 1);
function uwp_account_available_tabs_cb($tabs)
{

    $tabs['courses'] = array(
        'title' => __('Meine Kurse', 'userswp'),
        'icon'  => 'fas fa-graduation-cap',
    );

    return $tabs;
}
//this is a test
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
        $columnsNeeded = 0;
        $column = 0;
        $row = 1;
        if ($courseAmount <= 1) {
            $columnsNeeded = 1;
        } else {
            $columnsNeeded = 2;
        }
        echo "<div class=\"courses\" style=\"display: grid; grid-template-columns: repeat($columnsNeeded, 1fr); column-gap: 1rem; row-gap: 1rem; grid-template-rows: repeat($rowsNeeded, 1fr);\">";
        foreach ($registeredCourses as $registeredCourse) {
            if ($registeredCourse == null or $registeredCourse == "" or $registeredCourse < 1) {
                continue;
            }
            $column++;
            if ($column > $columnsNeeded) {
                $row++;
                $column = 1;
            }
            $course = $wpdb->get_row("SELECT * FROM $wpdb->prefix" . "courses WHERE ID = " . $registeredCourse);
            $cDate = new DateTime($course->date);
            $courseDate = $cDate->format('d.m.Y H:i');
            echo "<div class=\"course\" style=\"grid-column: $column; grid-row: $row; \">
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
        echo "</div>";
    }
}
