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
$occur_id = isset($_GET['mc_id']) ? $_GET['mc_id'] : 0;
$event_id = $wpdb->get_var("SELECT occur_event_id FROM $wpdb->prefix" . "my_calendar_events WHERE occur_id = $occur_id");
$course = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courses WHERE event_id = $event_id");
$available = $course[0]->max_registrations - $course[0]->registrations;
$date = date('Y-m-d H:i:s');
if ($date > $course[0]->date) {
    echo "Anmeldung nicht mehr möglich";
    return;
} else {
    echo "Noch $available Plätze verfügbar";
    if ($available > 0 && is_user_logged_in()) {
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
justify-content: center;\" href=\"/registercourse?event_id=$event_id&occur_id=$occur_id\">Anmelden</a>";
    } else {
        echo "
    <br>
    <form action=\"/registercourse?event_id=$event_id&occur_id=$occur_id\" method=\"post\">
    <label for=\"email\">Email Addresse:</label>
    <input type=\"email\" id=\"email\" name=\"email\" required>
    <input type=\"submit\" value=\"Anmelden\" style=\"text-align: center;
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
    justify-content: center;\">
    </form>
";
    }
}
