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

//initialize variables for membership purchase or course purchase w/o membership
if ($order_id != null) {
    $order = wc_get_order($order_id);
    $wc_order_item = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "woocommerce_order_items WHERE order_id = $order_id");
    $order_item_id = $wc_order_item[0]->order_item_id;
    $wc_order_itemmeta = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "woocommerce_order_itemmeta WHERE order_item_id = $order_item_id AND meta_key = '_product_id'");
    $product_id = $wc_order_itemmeta[0]->meta_value;
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
            if ($order == $silver) {
                $membership = 1;
                $membershipStr = "halbes Jahr";
                if ($valid_until > $date) {
                    $valid_until_new = date('Y-m-d', strtotime($valid_until . ' + 6 months'));
                } elseif ($valid_until < $date) {
                    $valid_until_new = date('Y-m-d', strtotime($date . ' + 6 months'));
                }
            } elseif ($order == $gold) {
                $membership = 2;
                $membershipStr = "Jahr";
                if ($valid_until > $date) {
                    $valid_until_new = date('Y-m-d', strtotime($valid_until . ' + 1 years'));
                } elseif ($valid_until < $date) {
                    $valid_until_new = date('Y-m-d', strtotime($date . ' + 1 years'));
                }
            }
            //update database entry for membership
            $table = $wpdb->prefix . 'users';
            $data = array('membership' => $membership);
            $where = array('ID' => $userID);
            $wpdb->update($table, $data, $where);
            $data = array('subscription_valid_until' => $valid_until_new);
            $wpdb->update($table, $data, $where);
            /**
             * TO DO:
             * DONT UPDATE AGAIN IF PAGE IS RELOADED
             * 
             */
            $subject = "Mitgliedschaft";
            $message = "
            <html>
            <div class=\"container\" style=\"border: 1px solid black;\">
<div class=\"content\" style=\"padding: 5px;\">
<p>Hallo $first_name $last_name</p>
<p>Vielen Dank für den kauf von der Mitgliedschaft für ein \"$membershipStr\".</p>
<p>Wir freuen uns Sie bald in unseren Kursen zu treffen.</p>
<p>Freundliche Grüße</p>
</div>
</div>
</html>
            ";
            $_SESSION['mailSent'] = $_SESSION['mailSent'] ? true : wp_mail($customerEmail, $subject, $message, $headers);
            /**
             * TO DO:
             * SEND EMAIL ONLY ONCE
             * 
             */
        } else {
            $courseInfo = $wpdb->get_row("SELECT * FROM $wpdb->prefix" . "courses WHERE product_id = $product_id;");
            if ($courseInfo->registrations >= $courseInfo->max_registrations and $_SESSION['mailSent'] == false and $alreadyRegistered == false) {
                $thanksMessage = "Dieser Kurs ist bereits ausgebucht.";
                echo "<div style=\"text-align:center;\">$thanksMessage</div>";
            } else {
                $customerEmail = wp_get_current_user()->user_email;
                if (str_contains($registered_courses, ";" . $courseInfo->id . ';') or str_contains($courseInfo->registered_emails, ";" . $customerEmail . ';')) {
                    if ($_SESSION['mailSent'] == false) {
                        $thanksMessage = "Sie sind bereits angemeldet für diesen Kurs.";
                        $alreadyRegistered = true;
                    }
                }
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
        <div class=\"container\" style=\"border: 1px solid black;\">
        <div class=\"content\" style=\"padding: 5px;\">
        <p>Hallo $first_name $last_name</p>
        <p>Vielen Dank für den kauf vom Kurs \"$course_name\".</p>
        <p>Treten sie dem Kurs am $course_date über folgenden Link bei:\n$course_link</p>
        <p>Freundliche Grüße</p>
        </div>
        </div>
        </html>";
                    $_SESSION['mailSent'] = $_SESSION['mailSent'] ? true : wp_mail($customerEmail, $subject, $message, $headers);
                    /**
                     * TO DO:
                     * SEND EMAIL ONLY ONCE
                     * 
                     */
                } else {
                    echo "<div style=\"text-align:center;\">$thanksMessage</div>";
                }
            }
        }
    } else {
        if (!str_contains($courseInfo->registered_emails, ";" . $customerEmail . ';')) {
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
            /**
             * TO DO:
             * DONT UPDATE AGAIN IF PAGE IS RELOADED
             * 
             */

            $subject = "Yoga Kurs";
            $course_name = $courseInfo->course_name;
            $course_date = $courseInfo->date;
            $course_link = $courseInfo->url;

            $message = "<html>
    <div class=\"container\" style=\"border: 1px solid black;\">
    <div class=\"content\" style=\"padding: 5px;\">
    <p>Hallo $first_name $last_name</p>
    <p>Vielen Dank für den kauf vom Kurs \"$course_name\".</p>
    <p>Treten sie dem Kurs am $course_date über folgenden Link bei:\n$course_link</p>
    <p>Freundliche Grüße</p>
    </div>
    </div>
    </html>";
            $_SESSION['mailSent'] = $_SESSION['mailSent'] ? true : wp_mail($customerEmail, $subject, $message, $headers);
            /**
             * TO DO:
             * SEND EMAIL ONLY ONCE
             * 
             */
        }
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
                    $thanksMessage = "Sie sind bereits angemeldet für diesen Kurs.";
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
<div class=\"container\" style=\"border: 1px solid black;\">
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
                /**
                 * TO DO:
                 * SEND EMAIL ONLY ONCE
                 * 
                 */
            }
