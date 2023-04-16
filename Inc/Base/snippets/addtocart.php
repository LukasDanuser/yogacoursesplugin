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

global $woocommerce;
global $wpdb;
session_start();
$_SESSION['mailSent'] = false;
$occur_id = isset($_GET['occur_id']) ? $_GET['occur_id'] : 0;
$event_id = $wpdb->get_var("SELECT occur_event_id FROM $wpdb->prefix" . "my_calendar_events WHERE occur_id = $occur_id");
$course_id = $wpdb->get_var("SELECT id FROM $wpdb->prefix" . "courses WHERE event_id = $event_id");
if (isset($_GET['membership'])) {
    $href = "";
    $annual = $wpdb->get_var("SELECT membership_productID FROM $wpdb->prefix" . "courseSettings WHERE membership_type = 'annual'");
    $semiAnnual = $wpdb->get_var("SELECT membership_productID FROM $wpdb->prefix" . "courseSettings WHERE membership_type = 'semiannual'");
    $productID = $_GET['membership'] == 'jahr' ? $annual : ($_GET['membership'] == 'halb' ? $semiAnnual : 'invalid');
    if ($productID == 'invalid') {
        echo "<script>
        window.location.href = \"/\";
    </script>";
    }
    if (is_user_logged_in()) {
        $href = "checkout";
    } else {
        $href = "register";
    }
    WC()->cart->empty_cart();
    $woocommerce->cart->add_to_cart($productID);

    if ($href == "register") {
?> <script>
            window.location.href = "/register";
        </script><?php
                    exit;
                } elseif ($href == "checkout") {
                    ?> <script>
            window.location.href = "/checkout";
        </script><?php
                    exit;
                }
            } else if (isset($_GET['id'])) {
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                if (is_user_logged_in()) {
                    $userID = wp_get_current_user()->ID;
                    $userEmail = wp_get_current_user()->user_email;
                    $table_name = $wpdb->prefix . "courseOccur";
                    $sql =
                        "INSERT INTO $table_name (occur_id, occur_event_id, course_id, registered_user_id, registered_user_email, verified) VALUES (
                        $occur_id,
                        $event_id,
                        $course_id,
                        $userID,
                        \"$userEmail\",
                        0)";
                    dbDelta($sql);
                } else {
                    $email = isset($_GET['email']) ? $_GET['email'] : '';
                    $table_name = $wpdb->prefix . "courseOccur";
                    $sql =
                        "INSERT INTO $table_name (occur_id, occur_event_id, course_id, registered_user_id, registered_user_email, verified) VALUES (
                    $occur_id,
                    $event_id,
                    $course_id,
                    0,
                    \"$email\",
                    0)";
                    dbDelta($sql);
                }
                $productID = $_GET['id'];
                WC()->cart->empty_cart();
                $woocommerce->cart->add_to_cart($productID);
                    ?> <script>
        window.location.href = "/checkout";
    </script><?php
                exit;
            } else {
                ?> <script>
        window.location.href = "/";
    </script><?php
                exit;
            }
