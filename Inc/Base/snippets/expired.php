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
$userID = get_current_user_id();
$valid_until = $wpdb->get_var("SELECT subscribtion_valid_until FROM wp_users WHERE ID = $userID");
$date = date("Y\-m\-d");
if ($date > $valid_until) {
    $table = 'wp_users';
    $data = array('membership' => '0');
    $where = array('ID' => $userID);
    $wpdb->update($table, $data, $where);
    $data = array('subscribtion_valid_until' => '0000-00-00');
    $wpdb->update($table, $data, $where);
} else { ?><script>
        window.location.href = "/courses";
    </script><?php
                exit;
            }

                ?>
<div style="text-align: center;">
    <p>Your membership has expired!</p>
    <p>You can renew it <a href="/membership">here!</a></p>
</div>