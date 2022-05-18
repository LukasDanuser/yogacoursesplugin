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

wp_mail($to, $subject, $message, $headers) == true ? $mailSent = "Mail sent to $to" : $mailSent = "Mail not sent to $to";
echo "<div style=\"float: center;\">Vielen Dank für Ihren Einkauf bei uns!\n$mailSent</div>";
