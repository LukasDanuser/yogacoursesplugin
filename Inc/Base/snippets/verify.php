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

//initialize variables
global $wpdb;
session_start();
$_SESSION['mailSent'] = isset($_SESSION['mailSent']) ? $_SESSION['mailSent'] : false;
$order_id = isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : null;
$product_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
$date = date("Y-m-d");
$datetime = date('Y-m-d H:i:s');
$headers = array('Content-Type: text/html; charset=UTF-8');
$message = "";
$subject = "";
$alreadyRegistered = false;
$newRegisteredCourses = "";
$thanksMessage = "Vielen Dank für Ihren Einkauf bei uns!";
$courseInfo = isset($_REQUEST['id']) ? $wpdb->get_row("SELECT * FROM $wpdb->prefix" . "courses WHERE product_id = $product_id;") : null;
$userID = is_user_logged_in() ? get_current_user_id() : null;
$valid_until = is_user_logged_in() ? $wpdb->get_var("SELECT subscription_valid_until FROM $wpdb->prefix" . "users WHERE ID = $userID") : null;
$membership = is_user_logged_in() ? $wpdb->get_var("SELECT membership FROM $wpdb->prefix" . "users WHERE ID = $userID") : null;
$registered_courses = is_user_logged_in() ? $wpdb->get_var("SELECT registered_courses FROM $wpdb->prefix" . "users WHERE ID = $userID") : null;
$refund = false;
$refund_reason = "";


//initialize variables for membership purchase or course purchase w/o membership
if ($order_id != null) {
    $wc_order_item = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "woocommerce_order_items WHERE order_id = $order_id");
    $order_item_id = $wc_order_item[0]->order_item_id;
    $wc_order_itemmeta = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "woocommerce_order_itemmeta WHERE order_item_id = $order_item_id AND meta_key = '_product_id'");
    $product_id = $wc_order_itemmeta[0]->meta_value;
    $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courseOrders WHERE order_id = $order_id") == null ? $wpdb->insert(
        $wpdb->prefix . 'courseOrders',
        array(
            'order_id' => $order_id,
            'completed' => false,
            'user_id' => $userID,
            'product_id' => $product_id,
            'order_date' => $datetime,
            'refund' => false
        )
    ) : "";
    $course_orders =  $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courseOrders WHERE order_id = $order_id");
    $order = wc_get_order($order_id);
    $newRegisteredEmails = "";

    $gold = 30;
    $silver = 32;
    $membership = 0;
    $membershipStr = "";
    $valid_until_new = "0000-00-00";
    $first_name = $order->get_shipping_first_name();
    $last_name = $order->get_shipping_last_name();
    $customerEmail = $order->get_billing_email();

    //if membership is bought
    if (is_user_logged_in()) {
        if ($product_id == $gold or $product_id == $silver) {
            $thanksMessage = "Vielen Dank für Ihren Einkauf bei uns!";
            if ($product_id == $silver) {
                $membership = 1;
                $membershipStr = "halbes Jahr";
                if ($valid_until > $date) {
                    $valid_until_new = date('Y-m-d', strtotime($valid_until . ' + 6 months'));
                } elseif ($valid_until < $date) {
                    $valid_until_new = date('Y-m-d', strtotime($date . ' + 6 months'));
                }
            } elseif ($product_id == $gold) {
                $membership = 2;
                $membershipStr = "Jahr";
                if ($valid_until > $date) {
                    $valid_until_new = date('Y-m-d', strtotime($valid_until . ' + 1 years'));
                } elseif ($valid_until < $date) {
                    $valid_until_new = date('Y-m-d', strtotime($date . ' + 1 years'));
                }
            }
            //update database entry for membership
            if ($course_orders[0]->completed == false) {
                $table = $wpdb->prefix . 'users';
                $data = array('membership' => $membership);
                $where = array('ID' => $userID);
                $wpdb->update($table, $data, $where);
                $data = array('subscription_valid_until' => $valid_until_new);
                $wpdb->update($table, $data, $where);
                $table = $wpdb->prefix . 'courseOrders';
                $data = array('completed' => true);
                $where = array('order_id' => $order_id);
                $wpdb->update($table, $data, $where);
            }
            /**
             * TO DO:
             * DONT UPDATE AGAIN IF PAGE IS RELOADED
             * 
             */
            $subject = "Mitgliedschaft";
            $message = "
            <html>
            <div class=\"container\" style=\"border: 1px solid orange; border-radius: .25rem;\">
<div class=\"content\" style=\"padding: 5px;\">
<p>Hallo $first_name $last_name</p>
<p>Vielen Dank für den kauf von der Mitgliedschaft für ein \"$membershipStr\".</p>
<p>Wir freuen uns Sie bald in unseren Kursen zu treffen.</p>
<p>Freundliche Grüße</p>
</div>
</div>
</html>
            ";

            echo "<div style=\"text-align:center;\">$thanksMessage</div>";
            $_SESSION['mailSent'] = $_SESSION['mailSent'] ? true : wp_mail($customerEmail, $subject, $message, $headers);
        } else {
            $courseInfo = $wpdb->get_row("SELECT * FROM $wpdb->prefix" . "courses WHERE product_id = $product_id;");
            if ($courseInfo->registrations >= $courseInfo->max_registrations and $_SESSION['mailSent'] == false and $alreadyRegistered == false) {
                $thanksMessage = "Dieser Kurs ist bereits ausgebucht.";
                echo "<div style=\"text-align:center;\">$thanksMessage</div>";
                $refund = true;
                $refund_reason = "Der Kurs ist bereits ausgebucht.";
            } else {
                $customerEmail = wp_get_current_user()->user_email;
                if (str_contains($registered_courses, ";" . $courseInfo->id . ';') or str_contains($courseInfo->registered_emails, ";" . $customerEmail . ';')) {
                    if ($_SESSION['mailSent'] == false) {
                        $thanksMessage = "Sie sind für diesen Kurs bereits angemeldet.";
                        $alreadyRegistered = true;
                        $courseOrder = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courseOrders WHERE order_id = $order_id");
                        if ($courseOrder[0]->completed == false and $courseOrder[0]->refund == false) {
                            $refund = true;
                            $refund_reason = "Sie sind für diesen Kurs bereits angemeldet.";
                        }
                    }
                }
                if ($_SESSION['mailSent'] == false and $alreadyRegistered == false and $course_orders[0]->completed == false) {
                    if ($registered_courses == "0" or $registered_courses == "" or $registered_courses == " " or $registered_courses == ";") {
                        $newRegisteredCourses = ";" . $courseInfo->id . ";";
                    } else {
                        $newRegisteredCourses = $registered_courses . $courseInfo->id . ";";
                    }
                    $table = $wpdb->prefix . 'users';
                    $data = array('registered_courses' => $newRegisteredCourses);
                    $where = array('ID' => $userID);
                    $wpdb->update($table, $data, $where);
                    $table = $wpdb->prefix . 'courses';
                    $data = array('registrations' => $courseInfo->registrations + 1);
                    $where = array('product_id' => $product_id);
                    $wpdb->update($table, $data, $where);

                    $newRegisteredEmails = "";
                    if ($courseInfo->registered_emails == '' or $courseInfo->registered_emails == null or $courseInfo->registered_emails == ' ') {
                        $newRegisteredEmails = ';' . $customerEmail . ';';
                    } else {
                        $newRegisteredEmails = $courseInfo->registered_emails . $customerEmail . ';';
                    }
                    $data = array('registered_emails' => $newRegisteredEmails);
                    $wpdb->update($table, $data, $where);
                    echo "<div style=\"text-align:center;\">$thanksMessage</div>";
                    $subject = "Yoga Kurs";
                    $course_name = $courseInfo->course_name;
                    $course_date = $courseInfo->date;
                    $course_link = $courseInfo->url;

                    $message = "<html>
        <div class=\"container\" style=\"border: 1px solid orange; border-radius: .25rem;\">
        <div class=\"content\" style=\"padding: 5px;\">
        <p>Hallo $first_name $last_name</p>
        <p>Vielen Dank für den kauf vom Kurs \"$course_name\".</p>
        <p>Treten sie dem Kurs am $course_date über folgenden Link bei:\n$course_link</p>
        <p>Freundliche Grüße</p>
        </div>
        </div>
        </html>";
                    $table = $wpdb->prefix . 'courseOrders';
                    $data = array('completed' => true);
                    $where = array('order_id' => $order_id);
                    $wpdb->update($table, $data, $where);
                    $_SESSION['mailSent'] = $_SESSION['mailSent'] ? true : wp_mail($customerEmail, $subject, $message, $headers);
                } else {
                    echo "<div style=\"text-align:center;\">$thanksMessage</div>";
                }
            }
        }
    } else {
        $thanksMessage = "Vielen Dank für Ihren Einkauf bei uns!";
        $courseInfo = $wpdb->get_row("SELECT * FROM $wpdb->prefix" . "courses WHERE product_id = $product_id;");
        if ($courseInfo->registrations >= $courseInfo->max_registrations and $_SESSION['mailSent'] == false and $alreadyRegistered == false) {
            $thanksMessage = "Dieser Kurs ist bereits ausgebucht.";
            $courseOrder = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courseOrders WHERE order_id = $order_id");
            if ($courseOrder[0]->completed == false and $courseOrder[0]->refund == false) {
                $refund = true;
                $refund_reason = "Dieser Kurs ist bereits ausgebucht.";
            }
        } else if (!str_contains($courseInfo->registered_emails, ";" . $customerEmail . ';') and $course_orders[0]->completed == false) {
            $table = $wpdb->prefix . 'courses';
            $data = array('registrations' => $courseInfo->registrations + 1);
            $where = array('product_id' => $product_id);
            $wpdb->update($table, $data, $where);

            if ($courseInfo->registered_emails == '' or $courseInfo->registered_emails == null or $courseInfo->registered_emails == ' ') {
                $newRegisteredEmails = ';' . $customerEmail . ';';
            } else {
                $newRegisteredEmails = $courseInfo->registered_emails . $customerEmail . ';';
            }
            $data = array('registered_emails' => $newRegisteredEmails);
            $wpdb->update($table, $data, $where);
            $subject = "Yoga Kurs";
            $course_name = $courseInfo->course_name;
            $course_date = $courseInfo->date;
            $course_link = $courseInfo->url;

            $table = $wpdb->prefix . 'courseOrders';
            $data = array('completed' => true);
            $where = array('order_id' => $order_id);
            $wpdb->update($table, $data, $where);
            $message = "<html>
    <div class=\"container\" style=\"border: 1px solid orange; border-radius: .25rem;\">
    <div class=\"content\" style=\"padding: 5px;\">
    <p>Hallo $first_name $last_name</p>
    <p>Vielen Dank für den kauf vom Kurs \"$course_name\".</p>
    <p>Treten sie dem Kurs am $course_date über folgenden Link bei:\n$course_link</p>
    <p>Freundliche Grüße</p>
    </div>
    </div>
    </html>";
            $_SESSION['mailSent'] = $_SESSION['mailSent'] ? true : wp_mail($customerEmail, $subject, $message, $headers);
        }
        echo "<div style=\"text-align:center;\">$thanksMessage</div>";
    }
} else if ($product_id != null) {


    if ($courseInfo->registrations >= $courseInfo->max_registrations and $_SESSION['mailSent'] == false and $alreadyRegistered == false) {
        $thanksMessage = "Dieser Kurs ist bereits ausgebucht.";
        echo "<div style=\"text-align:center;\">$thanksMessage</div>";
    } else {
        if (is_user_logged_in()) {
            $customerEmail = wp_get_current_user()->user_email;
            if (str_contains($registered_courses, ";" . $courseInfo->id . ';') or str_contains($courseInfo->registered_emails, ";" . $customerEmail . ';')) {
                if ($_SESSION['mailSent'] == false) {
                    $thanksMessage = "Sie sind für diesen Kurs bereits angemeldet.";
                    $alreadyRegistered = true;
                }
            }

            if ($valid_until != "0000-00-00" and $membership != "0") {
                if ($date < $valid_until) {
                    if ($_SESSION['mailSent'] == false and $alreadyRegistered == false) {
                        if ($registered_courses == "0" or $registered_courses == "" or $registered_courses == " " or $registered_courses == ";") {
                            $newRegisteredCourses = ";" . $courseInfo->id . ";";
                        } else {
                            $newRegisteredCourses = $registered_courses . $courseInfo->id . ";";
                        }
                        $table = $wpdb->prefix . 'users';
                        $data = array('registered_courses' => $newRegisteredCourses);
                        $where = array('ID' => $userID);
                        $wpdb->update($table, $data, $where);
                        $courseInfo->registrations = $wpdb->get_var("SELECT registrations FROM $wpdb->prefix" . "courses WHERE product_id = $product_id");
                        $table = $wpdb->prefix . 'courses';
                        $data = array('registrations' => $courseInfo->registrations + 1);
                        $where = array('product_id' => $product_id);
                        $wpdb->update($table, $data, $where);
                        $newRegisteredEmails = "";
                        if ($courseInfo->registered_emails == '' or $courseInfo->registered_emails == null or $courseInfo->registered_emails == ' ') {
                            $newRegisteredEmails = ';' . $customerEmail . ';';
                        } else {
                            $newRegisteredEmails = $courseInfo->registered_emails . $customerEmail . ';';
                        }
                        $data = array('registered_emails' => $newRegisteredEmails);
                        $wpdb->update($table, $data, $where);
                        echo "<div style=\"text-align:center;\">$thanksMessage</div>";
                        $subject = "Yoga Kurs";
                        $course_name = $courseInfo->course_name;
                        $course_date = $courseInfo->date;
                        $course_link = $courseInfo->url;
                        $name = wp_get_current_user()->user_nicename;
                        $message = "<html>
<div class=\"container\" style=\"border: 1px solid orange; border-radius: .25rem;\">
<div class=\"content\" style=\"padding: 5px;\">
<p>Hallo $name</p>
<p>Vielen Dank für den kauf vom Kurs \"$course_name\".</p>
<p>Treten sie dem Kurs am $course_date über folgenden Link bei:\n$course_link</p>
<p>Freundliche Grüße</p>
</div>
</div>
</html>";
                    } else {
                        echo "<div style=\"text-align:center;\">$thanksMessage</div>";
                    }
                } else {
?><script>
                        window.location.href = "/courses";
                    </script><?php
                                exit;
                            }
                        } else {
                                ?><script>
                    window.location.href = "/courses";
                </script><?php
                            exit;
                        }
                    } else {
                            ?><script>
                window.location.href = "/courses";
            </script><?php
                        exit;
                    }
                }
                $_SESSION['mailSent'] = $_SESSION['mailSent'] ? true : wp_mail($customerEmail, $subject, $message, $headers);
            }
            if ($refund == true) {
                $order = wc_get_order($order_id);
                // If it's something else such as a WC_Order_Refund, we don't want that.
                if (!is_a($order, 'WC_Order')) {
                    return new WP_Error('wc-order', __('Provided ID is not a WC Order', 'courses-plugin'));
                }

                if ('refunded' == $order->get_status()) {
                    return new WP_Error('wc-order', __('Order has been already refunded', 'courses-plugin'));
                }


                // Get Items
                $order_items   = $order->get_items();

                // Refund Amount
                $refund_amount = 0;

                // Prepare line items which we are refunding
                $line_items = array();

                if ($order_items) {
                    foreach ($order_items as $item_id => $item) {

                        $tax_data = wc_get_order_item_meta($item_id, '_line_tax_data');

                        $refund_tax = 0;
                        if (is_array($tax_data["total"])) {

                            $refund_tax = array_map('wc_format_decimal', $tax_data["total"]);
                        }
                        $refund_amount = wc_format_decimal($refund_amount) + wc_format_decimal(wc_get_order_item_meta($item_id, '_line_total'));

                        $line_items[$item_id] = array(
                            'qty' => wc_get_order_item_meta($item_id, '_qty'),
                            'refund_total' => wc_format_decimal(wc_get_order_item_meta($item_id, '_line_total')),
                            'refund_tax' =>  $refund_tax
                        );
                    }
                }
                $refund = wc_create_refund(array(
                    'amount' => $refund_amount,
                    'reason' => $refund_reason,
                    'order_id' => $order_id,
                    'line_items' => $line_items,
                    'refund_payment' => true
                ));
                $table = $wpdb->prefix . 'courseOrders';
                $data = array('refund' => true);
                $where = array('order_id' => $order_id);
                $wpdb->update($table, $data, $where);
                $data = array('completed' => true);
                $wpdb->update($table, $data, $where);
                echo "<div style=\"text-align:center;\">Die Zahlung wird ihnen zurückerstattet.</div>";
                return $refund;
            }
