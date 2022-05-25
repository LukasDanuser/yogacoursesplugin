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
$rowsNeeded = ceil(sizeof($results) / 4);
$courseAmount = sizeof($results);
$columnsNeeded = $courseAmount >= 4 ? 4 : 3;
$columnsNeeded = $courseAmount == 2 ? 2 : $columnsNeeded;
$centerThree = $courseAmount == 1 ? true : false;
$plugin_data = get_plugin_data(dirname(__FILE__, 2) . '/coursesplugin/courses-plugin.php');
$plugin_version = $plugin_data['Version'];
wp_enqueue_style('style', '/wp-content/plugins/coursesplugin/assets/style.css', __FILE__, $plugin_version);
if (is_user_logged_in()) {
    $userID = get_current_user_id();
    $membership = $wpdb->get_var("SELECT membership FROM $wpdb->prefix" . "users WHERE ID = $userID");
    $valid_until = $wpdb->get_var("SELECT subscription_valid_until FROM $wpdb->prefix" . "users WHERE ID = $userID");
    $date = date("Y-m-d");
    if ($valid_until != "0000-00-00" and $membership != "0") {
        if ($date > $valid_until) {
            echo "Ihre Mitgliedschaft ist abgelaufen!\nSie können sie <a href=\"/membership\">hier</a> verlängern.";
            echo "<div class=\"courses\" style=\"display: grid; grid-template-columns: repeat($columnsNeeded, 1fr); column-gap: 1rem; row-gap: 1rem; grid-template-rows: repeat($rowsNeeded, 1fr);\">";
            if (!empty($results)) {
                $t1 = 0;
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
                    $t1++;
                    if ($t1 > $columnsNeeded) {
                        $t2++;
                        $t1 = 1;
                    }
                    $cDate = new DateTime($row->date);
                    $courseDate = $cDate->format('d.m.Y H:i');
                    $t1 = $centerThree == true ? 2 : $t1;
                    echo "
                    <div class=\"course\" style=\"grid-column: $t1; grid-row: $t2; margin-left: auto !important; margin-right: auto !important;\">
                    <div id=\"course$row->id\" class=\"course-card\">
                    <a href=\"/course?course=$row->id&product_id=$row->product_id\" style=\"text-decoration:none;\">
                    <div class=\"card-body\">
                    <h5 class=\"card-title text-center\"><b><u>
                                <p>$row->course_name</p>
                            </u></b></h5>
                    <p class=\"card-text\">$row->description</p>
                    <p><b>Datum:</b> $courseDate</p>
                    <p>$repeatTemp</p>
                    <div class=\"text-center\">
                        <label class=\"control-label\" >
                            <p>CHF $row->price</p>
                        </label>
                    </div>
                    </div>
                    </div></a>
                    </div>
                    ";
                }
                $table = $wpdb->prefix . 'users';
                $data = array('membership' => 0);
                $where = array('ID' => $userID);
                $wpdb->update($table, $data, $where);
                $data = array('subscription_valid_until' => '0000-00-00');
                $wpdb->update($table, $data, $where);
            }
        } else {
            echo "<div class=\"courses\" style=\"display: grid; grid-template-columns: repeat($columnsNeeded, 1fr); column-gap: 1rem; row-gap: 1rem; grid-template-rows: repeat($rowsNeeded, 1fr);\">";
            if (!empty($results)) {
                $t1 = 0;
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
                    $t1++;
                    if ($t1 > $columnsNeeded) {
                        $t2++;
                        $t1 = 1;
                    }
                    $cDate = new DateTime($row->date);
                    $courseDate = $cDate->format('d.m.Y H:i');
                    $t1 = $centerThree == true ? 2 : $t1;
                    if ($columnsNeeded == 2 || $columnsNeeded == 3) {
                        if ($t1 == 1) {
                            echo "<div class=\"course\" style=\"grid-column: $t1; grid-row: $t2; margin-left: auto !important;\">";
                        } elseif ($t1 == 2 && $columnsNeeded == 2) {
                            echo "<div class=\"course\" style=\"grid-column: $t1; grid-row: $t2; margin-right: auto !important;\">";
                        } elseif ($t1 == 3) {
                            echo "<div class=\"course\" style=\"grid-column: $t1; grid-row: $t2; margin-right: auto !important;\">";
                        } else {
                            echo "<div class=\"course\" style=\"grid-column: $t1; grid-row: $t2; margin-left: auto !important; margin-right: auto !important;\">";
                        }
                    } else {
                        echo "<div class=\"course\" style=\"grid-column: $t1; grid-row: $t2; margin-left: auto !important; margin-right: auto !important;\">";
                    }
                    echo "
                    <div id=\"course$row->id\" class=\"course-card\">
                    <a href=\"/course?course=$row->id&product_id=$row->product_id\" style=\"text-decoration:none;\">
                <div class=\"card-body\">
                    <h5 class=\"card-title text-center\"><b><u>
                                <p>$row->course_name</p>
                            </u></b></h5>
                    <p class=\"card-text\">$row->description</p>
                    <p><b>Datum:</b> $courseDate</p>
                    <p>$repeatTemp</p>
                </div>
            </div></a>
        </div>
                    ";
                }
            }
        }
    } else {
        echo "<div class=\"courses\" style=\"display: grid; grid-template-columns: repeat($columnsNeeded, 1fr); column-gap: 1rem; row-gap: 1rem; grid-template-rows: repeat($rowsNeeded, 1fr);\">";
        if (!empty($results)) {
            $t1 = 0;
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
                $t1++;
                if ($t1 > $columnsNeeded) {
                    $t2++;
                    $t1 = 1;
                }
                $cDate = new DateTime($row->date);
                $courseDate = $cDate->format('d.m.Y H:i');
                $t1 = $centerThree == true ? 2 : $t1;
                echo "
                <div class=\"course\" style=\"grid-column: $t1; grid-row: $t2; margin-left: auto !important; margin-right: auto !important;\">
                <div id=\"course$row->id\" class=\"course-card\">
                <a href=\"/course?course=$row->id&product_id=$row->product_id\" style=\"text-decoration:none;\">
                <div class=\"card-body\">
                    <h5 class=\"card-title text-center\"><b><u>
                                <p>$row->course_name</p>
                            </u></b></h5>
                    <p class=\"card-text\">$row->description</p>
                    <p><b>Datum:</b> $courseDate</p>
                    <p>$repeatTemp</p>
                    <div class=\"text-center\">
                        <label class=\"control-label\" >
                            <p>CHF $row->price</p>
                        </label>
                    </div>
                </div>
            </div></a>
        </div>
                ";
            }
        }
    }
} else {
    echo "<div class=\"courses\" style=\"display: grid; grid-template-columns: repeat($columnsNeeded, 1fr); column-gap: 1rem; row-gap: 1rem; grid-template-rows: repeat($rowsNeeded, 1fr);\">";
    if (!empty($results)) {
        $t1 = 0;
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
            $t1++;
            if ($t1 > $columnsNeeded) {
                $t2++;
                $t1 = 1;
            }
            $cDate = new DateTime($row->date);
            $courseDate = $cDate->format('d.m.Y H:i');
            $t1 = $centerThree == true ? 2 : $t1;
            echo "
            <div class=\"course\" style=\"grid-column: $t1; grid-row: $t2; margin-left: auto !important; margin-right: auto !important;\">
            <div id=\"course$row->id\" class=\"course-card\">
            <a href=\"/course?course=$row->id&product_id=$row->product_id\" style=\"text-decoration:none;\">
                <div class=\"card-body\">
                    <h5 class=\"card-title text-center\"><b><u>
                                <p>$row->course_name</p>
                            </u></b></h5>
                    <p class=\"card-text\">$row->description</p>
                    <p><b>Datum:</b> $courseDate</p>
                    <p>$repeatTemp</p>
                    <div class=\"text-center\">
                        <label class=\"control-label\" >
                            <p>CHF $row->price</p>
                        </label>
                    </div>
                </div>
            </div></a>
        </div>
            ";
        }
    }
}
echo "</div>";
