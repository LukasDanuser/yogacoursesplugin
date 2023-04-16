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
global $wbpd;
$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : 0;
$occur_id = isset($_GET['occur_id']) ? $_GET['occur_id'] : 0;
$email = isset($_POST['email']) ? $_POST['email'] : '';
$course = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courses WHERE event_id = $event_id");
$name = $course[0]->course_name;
$cDate = new DateTime($course[0]->date);
$price = $course[0]->price;
$description = $course[0]->description;

$course_id = $course[0]->id;
$product_id = $course[0]->product_id;
$available = $course[0]->max_registrations - $course[0]->registrations;
$courseDate = $cDate->format('d.m.Y H:i');
$registered_emails = $wpdb->get_var("SELECT registered_emails FROM $wpdb->prefix" . "courses WHERE product_id = $product_id");
$alreadyRegistered = false;
echo "<div class=\"course\">";
if (is_user_logged_in()) {
    if (str_contains($registered_emails, ";" . wp_get_current_user()->user_email . ';')) {
        $alreadyRegistered = true;
    }
    $userID = get_current_user_id();
    $membership = $wpdb->get_var("SELECT membership FROM $wpdb->prefix" . "users WHERE ID = $userID");
    $valid_until = $wpdb->get_var("SELECT subscription_valid_until FROM $wpdb->prefix" . "users WHERE ID = $userID");
    $date = date("Y-m-d");
    if ($alreadyRegistered == true) {
        echo "<div style=\"text-align:center;\"><p>Sie sind für diesen Kurs bereits angemeldet.</p></div>";
    } else if ($available <= 0 and $available != null) {
        echo "<div style=\"text-align:center;\"><p>Dieser Kurs ist leider nicht mehr verfügbar.</p><p>Sie können <a href=\"/courses\">hier</a> andere Kurse anschauen.</p></div>";
    } else {
        if ($valid_until != "0000-00-00" and $membership != "0") {
            if ($date > $valid_until) {
                echo " <script>window.location.replace(\"/addtocart?id=$product_id&occur_id=$occur_id&href=checkout\");</script>";
            } else {
                echo " <script>window.location.replace(\"/thanks?id=$product_id&occur_id=$occur_id\");</script>";
            }
        } else {
            echo " <script>window.location.replace(\"/addtocart?id=$product_id&occur_id=$occur_id&href=checkout\");</script>";
        }
    }
} else {
    echo " <script>window.location.replace(\"/addtocart?id=$product_id&occur_id=$occur_id&email=$email&href=register\");</script>";
}
echo "</div>";
