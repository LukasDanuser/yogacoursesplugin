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
$entry_exists = false;
$gold = 30;
$silver = 32;
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 0;

if (isset($_GET['order_id'])) {
    while ($entry_exists == false) {
        $query = "SELECT * FROM $wpdb->prefix" . "wc_order_product_lookup WHERE order_id = $order_id";
        $result = $wpdb->get_results($query);
        if ($result) {
            $entry_exists = true;
        } else {
            $entry_exists = false;
            sleep(0.01);
        }
    }

    $membership = 0;
    $membershipStr = "";
    $valid_until_new = "0000-00-00";
    $userID = get_current_user_id();
    $order = (int)$wpdb->get_var("SELECT product_id FROM $wpdb->prefix" . "wc_order_product_lookup WHERE order_id = $order_id");
    $valid_until = $wpdb->get_var("SELECT subscription_valid_until FROM $wpdb->prefix" . "users WHERE ID = $userID");
    $order_status = $wpdb->get_var("SELECT verified FROM $wpdb->prefix" . "wc_order_product_lookup WHERE order_id = $order_id");
    $date = date("Y-m-d");
    if ($order_id != 0) {
        if ((int)$order_status != 1) {
            if ($order == $gold or $order == $silver) {
                if ($order == $silver) {
                    $membership = 1;
                    $membershipStr = "Silber";
                    if ($valid_until > $date) {
                        $valid_until_new = date('Y-m-d', strtotime($valid_until . ' + 6 months'));
                    } elseif ($valid_until < $date) {
                        $valid_until_new = date('Y-m-d', strtotime($date . ' + 6 months'));
                    }
                } elseif ($order == $gold) {
                    $membership = 2;
                    $membershipStr = "Gold";
                    if ($valid_until > $date) {
                        $valid_until_new = date('Y-m-d', strtotime($valid_until . ' + 1 years'));
                    } elseif ($valid_until < $date) {
                        $valid_until_new = date('Y-m-d', strtotime($date . ' + 1 years'));
                    }
                }

                if ($order == $silver or $order == $gold) {
                    $table = $wpdb->prefix . 'users';
                    $data = array('membership' => $membership);
                    $where = array('ID' => $userID);
                    $wpdb->update($table, $data, $where);
                    $data = array('subscription_valid_until' => $valid_until_new);
                    $wpdb->update($table, $data, $where);

                    $table = $wpdb->prefix . 'wc_order_product_lookup';
                    $data = array('verified' => '1');
                    $where = array('order_id' => $order_id);
                    $wpdb->update($table, $data, $where);
                }
            }
            $productID = $wpdb->get_var("SELECT product_id FROM $wpdb->prefix" . "wc_order_product_lookup WHERE order_id = $order_id");
            $results = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courses WHERE product_id = $productID");
            $order = wc_get_order($order_id);
            $first_name = $order->get_shipping_first_name();
            $last_name  = $order->get_shipping_last_name();
            $to = $order->get_billing_email();
            $subject = "Yoga Kurs";
            $course_name = "";
            $course_date = "";
            $course_link = "";
            foreach ($results as $courseInfo) {
                $course_name = $courseInfo->course_name;
                $course_date = $courseInfo->date;
                $course_link = $courseInfo->url;
            }

            if ($productID != $gold and $productID != $silver) {
                $message = "Guten Tag $first_name $last_name\n\nVielen Dank für Ihren Einkauf bei uns!\nSie haben sich für den Kurs \"$course_name\" entschieden.\n\nTreten sie dem Kurs am $course_date über folgenden Link bei:\n$course_link \n\nFreundliche Grüsse";
            } else {
                $message = "Guten Tag $first_name $last_name\n\nVielen Dank für Ihren Einkauf bei uns!\nSie haben sich für die $membershipStr Mitgliedschaft entschieden.\n\nFreundliche Grüsse";
            }
            mail($to, $subject, $message, "", "");
            $registrations = $wpdb->get_var("SELECT registrations FROM $wpdb->prefix" . "courses WHERE product_id = $productID");
            $table = $wpdb->prefix . 'courses';
            $data = array('registrations' => $registrations + 1);
            $where = array('product_id' => $productID);
            $wpdb->update($table, $data, $where);
            $table = $wpdb->prefix . 'wc_order_product_lookup';
            $data = array('verified' => '1');
            $where = array('order_id' => $order_id);
            $wpdb->update($table, $data, $where);
        } else { ?><script>
                window.location.href = "/courses";
            </script><?php
                        exit;
                    }
                }
                $orderDetails = wc_get_order($order_id);
                $payment_method = $orderDetails->get_payment_method();
                if ($payment_method == "woocommerce_payments") {
                    $payment_method = "Woocommerce Payment";
                } else {
                    $payment_method = "Payment";
                }
            }
            echo <<< EOL
<div style="text-align: center;">
<p>Vielen Dank für Ihren Einkauf!</p>
<p>Sie erhalten in kürze eine Email</p>
<p>Sie können sich <a href="/courses">hier</a> andere Kurse anschauen!</p>
EOL;
            if ($order_id == $silver or $order_id == $gold) {
                echo <<< EOL
<p>Sie haben die $membershipStr Mitgliedschaft gekauft!</p>
</div>
EOL;
            }
