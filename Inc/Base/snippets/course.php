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
$course_id = isset($_GET['course']) ? $_GET['course'] : 0;
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : 0;
$course = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courses WHERE id = $course_id");
$name = $course[0]->course_name;
$cDate = new DateTime($course[0]->date);
$price = $course[0]->price;
$description = $course[0]->description;

$product_id = $course[0]->product_id;
$available = $course[0]->max_registrations - $course[0]->registrations;
$courseDate = $cDate->format('d.m.Y H:i');
$registered_emails = $wpdb->get_var("SELECT registered_emails FROM $wpdb->prefix" . "courses WHERE product_id = $product_id");
$alreadyRegistered = false;
if (is_user_logged_in()) {
    if (str_contains($registered_emails, ";" . wp_get_current_user()->user_email . ';')) {
        $alreadyRegistered = true;
    }
    $userID = get_current_user_id();
    $membership = $wpdb->get_var("SELECT membership FROM $wpdb->prefix" . "users WHERE ID = $userID");
    $valid_until = $wpdb->get_var("SELECT subscription_valid_until FROM $wpdb->prefix" . "users WHERE ID = $userID");
    $date = date("Y-m-d");
    echo "<div class=\"course\">";
    if ($alreadyRegistered == true) {
        echo "<div style=\"text-align:center;\"><p>Sie sind bereits angemeldet für diesen Kurs.</p></div>";
    } else if ($available <= 0 and $available != null) {
        echo "<div style=\"text-align:center;\"><p>Dieser Kurs ist leider nicht mehr verfügbar.</p><p>Sie können <a href=\"/courses\">hier</a> andere Kurse anschauen.</p></div>";
    } else {
        if ($valid_until != "0000-00-00" and $membership != "0") {
            if ($date > $valid_until) {
                echo "
                    <a href=\"/addtocart?id=$product_id&href=checkout\" style=\"text-decoration:none;\">
                    <p>$name</p>
                    <p>$courseDate</p>
                    <p>CHF $price</p>
                    <p>$description</p>
                    ";
                if ($available != null and $available != '' and $available != ' ') {
                    echo "<p>$available Plätze verfügbar</p>";
                }
                echo "
                    </a>
                    ";
            } else {
                echo "
                    <a href=\"/thanks?id=$product_id\" style=\"text-decoration:none;\">
                    <p>$name</p>
                    <p>$courseDate</p>
                    <p>$description</p>";
                if ($available != null and $available != '' and $available != ' ') {
                    echo "<p>$available Plätze verfügbar</p>";
                }
                echo "
                    </a>
                    ";
            }
        } else {
            echo "
                    <a href=\"/addtocart?id=$product_id&href=checkout\" style=\"text-decoration:none;\">
                    <p>$name</p>
                    <p>$courseDate</p>
                    <p>CHF $price</p>
                    <p>$description</p>";
            if ($available != null and $available != '' and $available != ' ') {
                echo "<p>$available Plätze verfügbar</p>";
            }
            echo "
                    </a>
                    ";
        }
    }
} else {
    echo "
    <a href=\"/addtocart?id=$product_id&href=checkout\" style=\"text-decoration:none;\">
    <p>$name</p>
    <p>$courseDate</p>
    <p>CHF $price</p>
    <p>$description</p>";
    if ($available != null or $available != '' or $available != ' ') {
        echo "<p>$available Plätze verfügbar</p>";
    }
    echo "
    </a>
    ";
}
echo "</div>";
