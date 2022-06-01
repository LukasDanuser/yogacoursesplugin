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
        echo "<p style=\"border: 1px solid orange;max-width: fit-content;padding: 0.3rem;margin: auto;border-radius: 0.2rem;\">Diese Seite ist nur für Mitglieder zugänglich!<br>Sie können <a href=\"/membership\">hier</a> eine Mitgliedschaft kaufen.</p>";
        echo "</div>";
    }
}
