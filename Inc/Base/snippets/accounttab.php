<?php
add_filter('uwp_account_available_tabs', 'uwp_account_available_tabs_cb', 10, 1);
function uwp_account_available_tabs_cb($tabs)
{
    unset($tabs['notifications']);
    unset($tabs['privacy']);
    unset($tabs['account']);
    unset($tabs['change-password']);

    $tabs['account'] = array(
        'title' => __('Konto', 'userswp'),
        'icon'  => 'fas fa-user mr-1 fa-fw',
    );
    $tabs['courses'] = array(
        'title' => __('Meine Kurse', 'userswp'),
        'icon'  => 'fas fa-graduation-cap',
    );
    $tabs['videos'] = array(
        'title' => __('Videos', 'userswp'),
        'icon'  => 'fas fa-video mr-1 fa-fw',
    );
    $tabs['change-password'] = array(
        'title' => __('Passwort ändern', 'userswp'),
        'icon'  => 'fas fa-asterisk mr-1 fa-fw',
    );
    return $tabs;
}
add_filter('uwp_account_page_title', 'uwp_account_page_title_cb', 10, 2);
function uwp_account_page_title_cb($title, $type)
{
    if ($type == 'courses') {
        $title = __('Meine Kurse', 'uwp-messaging');
    }
    if ($type == 'videos') {
        $title = __('Videos', 'uwp-messaging');
    }

    return $title;
}

add_filter('uwp_account_form_display', 'uwp_account_form_display_cb', 10, 1);
function uwp_account_form_display_cb($type)
{
    global $wpdb;
    $current_user = wp_get_current_user();
    $display_name = $current_user->display_name;
    echo "<style> .card-body {display: none !important;}
                  .mt-0:hover {color: black !important;} </style>";
    echo "<script> 
    var element = document.querySelectorAll('a.mt-0');
    element[0].innerHTML = \"$display_name\";
    element[0].removeAttribute('href');
    element[0].removeAttribute('title');</script>";
    if ($type == 'courses') {

        $plugin_data = get_plugin_data(dirname(__FILE__, 3) . '/coursesplugin/courses-plugin.php');
        $plugin_version = $plugin_data['Version'];
        wp_enqueue_style('style', '/wp-content/plugins/coursesplugin/assets/style.css', __FILE__, $plugin_version);
        $date = date("Y-m-d");
        $userID = null;
        $membership = null;
        $valid_until = null;
        if (is_user_logged_in()) {
            $userID = get_current_user_id();
            $membership = $wpdb->get_var("SELECT membership FROM $wpdb->prefix" . "users WHERE ID = $userID");
            $valid_until = $wpdb->get_var("SELECT subscription_valid_until FROM $wpdb->prefix" . "users WHERE ID = $userID");
        }
        $expirationDate = new DateTime($valid_until);
        $registeredCourses = explode(";", $wpdb->get_var("SELECT registered_courses FROM $wpdb->prefix" . "users WHERE ID = " . get_current_user_id()));
        echo $membership > 0 ? ($date < $valid_until ? 'Ihre Mitgliedschaft ist noch gültig bis: ' . $expirationDate->format('d.m.Y') . '<br><br>' : 'Ihre Mitgliedschaft ist abgelaufen! Sie können sie <a href="/mitgliedschaft/#preise">hier</a> erneuern.<br><br>') : 'Sie sind kein Mitglied! Sie können <a href="/mitgliedschaft/#preise">hier</a> eine Mitgliedschaft kaufen.<br><br>';
        echo $wpdb->get_var("SELECT registered_courses FROM $wpdb->prefix" . "users WHERE ID = " . get_current_user_id()) == '0' ? 'Sie sind für keine Kurse angemeldet!' : 'Sie sind für folgende Kurse angemeldet<br><br>';
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
        $autooptin = wp_get_current_user()->autooptin;
        $autooptinnew = $autooptin == 0 ? 1 : 0;
        $autooptinbutton = $autooptin == 0 ? 'Automatisches anmelden einschalten' : 'Automatisches anmelden ausschalten';

        if (isset($_GET['autooptin'])) {
            global $wpdb;
            $userID = wp_get_current_user()->ID;
            $table = $wpdb->prefix . 'users';
            $data = array('autooptin' => $autooptinnew);
            $where = array('ID' => $userID);
            $wpdb->update($table, $data, $where);
            echo " <script>window.location.replace(\"/account?type=courses\");</script>";
        }
        echo "<br><br><a style=\"text-align: center;
background-color: #d9cdcd;
font-family: Open Sans,Helvetica, Arial, Sans-Serif, serif;
font-weight: 600;
font-size: 12px;
line-height: 1;
letter-spacing: 1px;
text-decoration: none;
text-transform: uppercase;
color: rgba(var(--kubio-color-6-variant-4),1);
border-top-color: rgba(217, 205, 205, 0.7);
border-top-width: 2px;
border-top-style: solid;
border-right-color: rgba(217, 205, 205, 0.7);
border-right-width: 2px;
border-right-style: solid;
border-bottom-color: rgba(217, 205, 205, 0.7);
border-bottom-width: 2px;
border-bottom-style: solid;
border-left-color: rgba(217, 205, 205, 0.7);
border-left-width: 2px;
border-left-style: solid;
border-top-left-radius: 5px;
border-top-right-radius: 5px;
border-bottom-left-radius: 5px;
border-bottom-right-radius: 5px;
padding-top: 12px;
padding-bottom: 12px;
padding-left: 24px;
padding-right: 24px;
justify-content: center;\" href=\"/account?type=courses&autooptin=$autooptinnew\">$autooptinbutton</a><br><br>";
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
            <p><a href=\"$course->url\">Link zum Kurs</a></p>
        </div>
    </div></a>
</div>
            ";
        }
        echo "</div>";
    }

    if ($type == 'videos') {
        $plugin_data = get_plugin_data(dirname(__FILE__, 3) . '/coursesplugin/courses-plugin.php');
        $plugin_version = $plugin_data['Version'];
        wp_enqueue_style('style', '/wp-content/plugins/coursesplugin/assets/style.css', __FILE__, $plugin_version);

        if (is_user_logged_in()) {
            $userID = get_current_user_id();
            $membership = $wpdb->get_var("SELECT membership FROM $wpdb->prefix" . "users WHERE ID = $userID");
            $valid_until = $wpdb->get_var("SELECT subscription_valid_until FROM $wpdb->prefix" . "users WHERE ID = $userID");
            $date = date("Y-m-d");
            if ($valid_until != "0000-00-00" and $membership != "0") {
                if ($date > $valid_until) {
                    echo "<div class=\"accessDenied\" style=\"text-align: center;\">";
                    echo "<p style=\"border: 1px solid orange;max-width: fit-content;padding: 0.3rem;margin: auto;border-radius: 0.2rem;\">Diese Seite ist nur für Mitglieder zugänglich!<br>Sie können <a href=\"/membership\">hier</a> eine Mitgliedschaft kaufen.</p>";
                    echo "</div>";
                } else {
                    $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    $videos = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courseVideos");
                    $rowsNeeded = ceil(sizeof($videos) / 4);
                    $courseAmount = sizeof($videos);
                    $columnsNeeded = $courseAmount >= 4 ? 4 : 3;
                    $columnsNeeded = $courseAmount == 2 ? 2 : $columnsNeeded;
                    $centerThree = $courseAmount == 1 ? true : false;
                    $maxWidth = ($courseAmount == 2) ? "50%" : (($courseAmount == 3)  ? "75%" : "100%");
                    if (!empty($videos)) {
                        $t1 = 0;
                        $t2 = 1;
                        echo "<style>.wp-container-7>* {
                    max-width: $maxWidth !important;
                }</style>";
                        echo "<div class=\"videos\" style=\"display: grid; grid-template-columns: repeat($columnsNeeded, 1fr); column-gap: 1rem; row-gap: 1rem; grid-template-rows: repeat($rowsNeeded, 1fr);\">";
                        foreach ($videos as $video) {
                            $t1++;
                            if ($t1 > $columnsNeeded) {
                                $t2++;
                                $t1 = 1;
                            }
                            $t1 = $centerThree == true ? 2 : $t1;
                            echo "
                    <div class=\"video\" style=\"grid-column: $t1; grid-row: $t2; margin-left: auto !important; margin-right: auto !important; max-width: 30rem !important;\">
                        <div id=\"video$video->id\" class=\"video-card\">
                            <div class=\"video-body\">
                                <h5 class=\"video-title text-center\"><b><u>
                                 <p>$video->video_name</p>
                                </u></b></h5>
                                <p class=\"video-text\">$video->video_description</p>
                                <video width=\"320\" height=\"240\" controls>
                                    <source src=\"$video->file_url\" type=\"video/mp4\">
                                    Your browser does not support the video tag.
                                </video> <br>
                            </div>
                        </div>
                    </div>";
                        }
                        echo "</div>";
                    }
                }
            } else {
                echo "<div class=\"accessDenied\" style=\"text-align: center;\">";
                echo "<p style=\"border: 1px solid orange;max-width: fit-content;padding: 0.3rem;margin: auto;border-radius: 0.2rem;\">Diese Seite ist nur für Mitglieder zugänglich!<br>Sie können <a href=\"/mitgliedschaft\">hier</a> eine Mitgliedschaft kaufen.</p>";
                echo "</div>";
            }
        }
    }

}
