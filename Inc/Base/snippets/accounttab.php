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
        echo "<br><a style=\"text-align: center;
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
}
