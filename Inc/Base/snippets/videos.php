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
wp_enqueue_style('style', '/wp-content/plugins/coursesplugin/assets/style.css', __FILE__, $plugin_version);

if (is_user_logged_in()) {
    $userID = get_current_user_id();
    $membership = $wpdb->get_var("SELECT membership FROM $wpdb->prefix" . "users WHERE ID = $userID");
    $valid_until = $wpdb->get_var("SELECT subscription_valid_until FROM $wpdb->prefix" . "users WHERE ID = $userID");
    $date = date("Y-m-d");
    if ($valid_until != "0000-00-00" and $membership != "0") {
        if ($date > $valid_until) {
            echo "Diese Seite ist nur für Mitglieder zugänglich!\nSie können <a href=\"/membership\">hier</a> eine Mitgliedschaft kaufen.";
        } else {
            $videos = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courseVideos");
            if (!empty($videos)) {
                $t1Video = 0;
                $t2Video = 1;
                echo "<div class=\"videos\" style=\"display: grid; grid-template-columns: repeat(5, minmax(20em, 1fr)); grid-gap: 1rem; grid-template-rows: repeat(auto-fill, minmax(20em, 1fr));\">";
                foreach ($videos as $video) {
                    $t1Video++;
                    if ($t1Video > 5) {
                        $t2Video++;
                        $t1Video = 1;
                    }
                    echo "
                  <div class=\"video\" style=\"grid-column: $t1Video; grid-row: $t2Video;\">
                  <div id=\"video$row->id\" class=\"video-card\">
                      <div class=\"video-body\">
                          <h5 class=\"video-title text-center\"><b><u>
                                      <p>$video->video_name</p>
                                  </u></b></h5>
                          <p class=\"video-text\">$video->video_description</p>
                          <video width=\"320\" height=\"240\" controls>
                    <source src=\"$video->file_url\" type=\"video/mp4\">
                    Your browser does not support the video tag.
                    </video> <br><br>
                          <div class=\"Löschen text-center\">
                              <button onclick=\"window.location.href='$actual_link&deleteVid=$video->id';\">Löschen</button>
                          </div>
                      </div>
                  </div>
            </div>";
                }
                echo "</div>";
            }
        }
    }
}
