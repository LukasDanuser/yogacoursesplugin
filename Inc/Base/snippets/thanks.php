<?php

/**
 * @package CoursesPlugin
 */

/*
Copyright (C) 2021  Rafisa Informatik GmbH

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

global $wpdb;
session_start();
$_SESSION['mailSent'] = isset($_SESSION['mailSent']) ? $_SESSION['mailSent'] : false;
$productID = isset($_GET['id']) ? $_GET['id'] : 0;
$courseInfo = $wpdb->get_row("SELECT * FROM $wpdb->prefix" . "courses WHERE product_id = $productID;");
$thanksMessage = "Vielen Dank für Ihren Einkauf bei uns!";
$alreadyRegistered = false;
if (is_user_logged_in()) {

    $userID = get_current_user_id();
    $registered_courses = $wpdb->get_var("SELECT registered_courses FROM $wpdb->prefix" . "users WHERE ID = $userID");
    if (str_contains($registered_courses, ";" . $courseInfo->id . ';')) {
        if ($_SESSION['mailSent'] == false) {
            $thanksMessage = "Sie sind bereits angemeldet für diesen Kurs.";
            $alreadyRegistered = true;
        }
    }
    $membership = $wpdb->get_var("SELECT membership FROM $wpdb->prefix" . "users WHERE ID = $userID");
    $valid_until = $wpdb->get_var("SELECT subscription_valid_until FROM $wpdb->prefix" . "users WHERE ID = $userID");
    $date = date("Y-m-d");
    if ($valid_until != "0000-00-00" and $membership != "0") {
        if ($date < $valid_until) {
            if ($productID == 0 || $productID == null) {
?><script>
                    window.location.href = "/courses";
                </script><?php
                            exit;
                        }
                        $name = wp_get_current_user()->display_name;
                        $to = wp_get_current_user()->user_email;

                        $subject = "Yoga Kurs";
                        $course_name = $courseInfo->course_name;
                        $course_date = $courseInfo->date;
                        $course_link = $courseInfo->url;

                        $message = "<html>

<div class=\"container\" style=\"border: 1px solid black;\">
<div class=\"content\" style=\"padding: 5px;\">
<p>Hallo $name</p>
<p>Vielen Dank für den kauf vom Kurs \"$course_name\".</p>
<p>Treten sie dem Kurs am $course_date über folgenden Link bei:\n$course_link</p>
<p>Freundliche Grüße</p>
</div>
</div>
</html>";

                        $headers = array('Content-Type: text/html; charset=UTF-8');
                        if ($_SESSION['mailSent'] == false && $alreadyRegistered == false) {
                            wp_mail($to, $subject, $message, $headers) == true ? $mailSent = "Mail sent to $to" : $mailSent = "Mail not sent to $to";
                            $newRegisteredCourses = "";
                            if ($registered_courses == "0" || $registered_courses == "" || $registered_courses == " " || $registered_courses == null) {
                                $newRegisteredCourses = ";" . $courseInfo->id . ";";
                            } else {
                                $newRegisteredCourses = $registered_courses . $courseInfo->id . ";";
                            }
                            $table = $wpdb->prefix . 'users';
                            $data = array('registered_courses' => $newRegisteredCourses);
                            $where = array('ID' => $userID);
                            $wpdb->update($table, $data, $where);
                            echo "<div style=\"text-align:center;\">$thanksMessage</div>";
                            $_SESSION['mailSent'] = true;
                        } else {
                            echo "<div style=\"text-align:center;\">$thanksMessage</div>";
                        }
                    } else {
                            ?><script>
                window.location.href = "/courses";
            </script><?php
                        exit;
                    }
                } else {
                        ?><script>
            window.location.href = "/courses";
        </script><?php
                    exit;
                }
            } else {
                    ?><script>
        window.location.href = "/courses";
    </script><?php
                exit;
            }
