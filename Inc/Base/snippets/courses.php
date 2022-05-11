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
$membership = 0;
$results = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courses ORDER BY date ASC");
$count = 0;
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$elementIDs = $wpdb->get_results("SELECT id FROM $wpdb->prefix" . "courses");
$repeatTemp = "";

if (is_user_logged_in()) {
    $userID = get_current_user_id();
    $membership = $wpdb->get_var("SELECT membership FROM $wpdb->prefix" . "users WHERE ID = $userID");
    $valid_until = $wpdb->get_var("SELECT subscription_valid_until FROM $wpdb->prefix" . "users WHERE ID = $userID");
    $date = date("Y-m-d");
    if ($valid_until != "0000-00-00" and $membership != "0") {
        if ($date > $valid_until) {
            echo "Ihre Mitgliedschaft ist abgelaufen!\nSie können sie <a href=\"/membership\">hier</a> verlängern.";
            echo "<div class=\"courses\" style=\"display: grid; grid-template-columns: auto; grid-gap: 10px; grid-auto-rows: minmax(100px, auto);\">";
            if (!empty($results)) {
                $t1 = 1;
                $t2 = 1;
                foreach ($results as $row) {
                    switch ($row->repeat_every) {
                        case "day":
                            $repeatTemp = "Täglich";
                            break;
                        case "week":
                            $repeatTemp = "Wöchentlich";
                            break;
                        case "month":
                            $repeatTemp = "Monatlich";
                            break;
                        case "2month":
                            $repeatTemp = "Alle 2 Monate";
                            break;
                        case "3month":
                            $repeatTemp = "Alle 3 Monate";
                            break;
                        case "4month":
                            $repeatTemp = "Alle 4 Monate";
                            break;
                        case "5month":
                            $repeatTemp = "Alle 5 Monate";
                            break;
                        case "6month":
                            $repeatTemp = "Alle 6 Monate";
                            break;
                        case "never":
                            $repeatTemp = "Einmalig";
                            break;
                    }
                    $count++;
                    if ($count > 5) {
                        $t2++;
                    }
                    $cDate = new DateTime($row->date);
                    $courseDate = $cDate->format('d.m.Y H:i');
                    echo "
                    <div class=\"course\" style=\"grid-column: $t1; grid-row: $t2;\">
                    <a href=\"/course?course=$row->id&product_id=$row->product_id\" style=\"text-decoration:none;\">
                    <p>$row->course_name</p>
                    <p>$courseDate</p>
                    <p>CHF $row->price</p>
                    <p>$row->description</p>
                    <p>$repeatTemp</p>
                    </a>
                    </div>
                    ";
                    $t1++;
                }
                $table = $wpdb->prefix . 'users';
                $data = array('membership' => 0);
                $where = array('ID' => $userID);
                $wpdb->update($table, $data, $where);
                $data = array('subscription_valid_until' => '0000-00-00');
                $wpdb->update($table, $data, $where);
            }
        } else {
            echo "<div class=\"courses\" style=\"display: grid; grid-template-columns: auto; grid-gap: 10px; grid-auto-rows: minmax(100px, auto);\">";
            if (!empty($results)) {
                $t1 = 1;
                $t2 = 1;
                foreach ($results as $row) {
                    switch ($row->repeat_every) {
                        case "day":
                            $repeatTemp = "Täglich";
                            break;
                        case "week":
                            $repeatTemp = "Wöchentlich";
                            break;
                        case "month":
                            $repeatTemp = "Monatlich";
                            break;
                        case "2month":
                            $repeatTemp = "Alle 2 Monate";
                            break;
                        case "3month":
                            $repeatTemp = "Alle 3 Monate";
                            break;
                        case "4month":
                            $repeatTemp = "Alle 4 Monate";
                            break;
                        case "5month":
                            $repeatTemp = "Alle 5 Monate";
                            break;
                        case "6month":
                            $repeatTemp = "Alle 6 Monate";
                            break;
                        case "never":
                            $repeatTemp = "Einmalig";
                            break;
                    }
                    $count++;
                    if ($count > 5) {
                        $t2++;
                    }
                    $cDate = new DateTime($row->date);
                    $courseDate = $cDate->format('d.m.Y H:i');
                    echo "
                    <div class=\"course\" style=\"grid-column: $t1; grid-row: $t2;\">
                    <a href=\"/course?course=$row->id&product_id=$row->product_id\" style=\"text-decoration:none;\">
                    <p>$row->course_name</p>
                    <p>$courseDate</p>
                    <p>$row->description</p>
                    <p>$repeatTemp</p>
                    </a>
                    </div>
                    ";
                    $t1++;
                }
            }
        }
    } else {
        echo "<div class=\"courses\" style=\"display: grid; grid-template-columns: auto; grid-gap: 10px; grid-auto-rows: minmax(100px, auto);\">";
        if (!empty($results)) {
            $t1 = 1;
            $t2 = 1;
            foreach ($results as $row) {
                switch ($row->repeat_every) {
                    case "day":
                        $repeatTemp = "Täglich";
                        break;
                    case "week":
                        $repeatTemp = "Wöchentlich";
                        break;
                    case "month":
                        $repeatTemp = "Monatlich";
                        break;
                    case "2month":
                        $repeatTemp = "Alle 2 Monate";
                        break;
                    case "3month":
                        $repeatTemp = "Alle 3 Monate";
                        break;
                    case "4month":
                        $repeatTemp = "Alle 4 Monate";
                        break;
                    case "5month":
                        $repeatTemp = "Alle 5 Monate";
                        break;
                    case "6month":
                        $repeatTemp = "Alle 6 Monate";
                        break;
                    case "never":
                        $repeatTemp = "Einmalig";
                        break;
                }
                $count++;
                if ($count > 5) {
                    $t2++;
                }
                $cDate = new DateTime($row->date);
                $courseDate = $cDate->format('d.m.Y H:i');
                echo "
                <div class=\"course\" style=\"grid-column: $t1; grid-row: $t2;\">
                <a href=\"/course?course=$row->id&product_id=$row->product_id\" style=\"text-decoration:none;\">
                <p>$row->course_name</p>
                <p>$courseDate</p>
                <p>CHF $row->price</p>
                <p>$row->description</p>
                <p>$repeatTemp</p>
                </a>
                </div>
                ";
                $t1++;
            }
        }
    }
} else {
    echo "<div class=\"courses\" style=\"display: grid; grid-template-columns: auto; grid-gap: 10px; grid-auto-rows: minmax(100px, auto);\">";
    if (!empty($results)) {
        $t1 = 1;
        $t2 = 1;
        foreach ($results as $row) {
            switch ($row->repeat_every) {
                case "day":
                    $repeatTemp = "Täglich";
                    break;
                case "week":
                    $repeatTemp = "Wöchentlich";
                    break;
                case "month":
                    $repeatTemp = "Monatlich";
                    break;
                case "2month":
                    $repeatTemp = "Alle 2 Monate";
                    break;
                case "3month":
                    $repeatTemp = "Alle 3 Monate";
                    break;
                case "4month":
                    $repeatTemp = "Alle 4 Monate";
                    break;
                case "5month":
                    $repeatTemp = "Alle 5 Monate";
                    break;
                case "6month":
                    $repeatTemp = "Alle 6 Monate";
                    break;
                case "never":
                    $repeatTemp = "Einmalig";
                    break;
            }
            $count++;
            if ($count > 5) {
                $t2++;
            }
            $cDate = new DateTime($row->date);
            $courseDate = $cDate->format('d.m.Y H:i');
            echo "
            <div class=\"course\" style=\"grid-column: $t1; grid-row: $t2;\">
            <a href=\"/course?course=$row->id&product_id=$row->product_id\" style=\"text-decoration:none;\">
            <p>$row->course_name</p>
            <p>$courseDate</p>
            <p>CHF $row->price</p>
            <p>$row->description</p>
            <p>$repeatTemp</p>
            </a>
            </div>
            ";
            $t1++;
        }
    }
}
echo "</div>";
