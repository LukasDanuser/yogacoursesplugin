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

$productID = isset($_GET['id']) ? $_GET['id'] : 0;
$courseInfo = $wpdb->get_row("SELECT * FROM $wpdb->prefix" . "courses WHERE product_id = $productID;");

$name = wp_get_current_user()->display_name;
$to = wp_get_current_user()->user_email;

$subject = "Yoga Kurs";
$course_name = $courseInfo->course_name;
$course_date = $courseInfo->date;
$course_link = $courseInfo->url;

$message = "Guten Tag $name \n\nVielen Dank für Ihren Einkauf bei uns!\nSie haben sich für den Kurs \"$course_name\" entschieden.\n\nTreten sie dem Kurs am $course_date über folgenden Link bei:\n$course_link \n\nFreundliche Grüsse!";

mail($to, $subject, $message, "", "");
echo "<div style=\"float: center;\">Vielen Dank für Ihren Einkauf bei uns!</div>";
