<?php
global $wpdb;
$entry_exists = false;
if (isset($_GET['order'])) {
    $order_id = $_GET['order'];
} else {
    $order_id = 0;
}
if (isset($_GET['order'])) {
    while ($entry_exists == false) {
        $query = "SELECT * FROM $wpdb->prefix.'_wc_order_product_lookup' WHERE order_id = $order_id";
        $result = $wpdb->get_results($query);
        if ($result) {
            $entry_exists = true;
        } else {
            $entry_exists = false;
            sleep(1);
        }
    }

    $membership = 0;
    $membershipStr = "";
    $valid_until_new = "0000-00-00";
    $userID = get_current_user_id();
    //   $result = $wpdb->get_var('SELECT order_id FROM ' . $wpdb->prefix . 'wc_order_stats' . ' ORDER BY order_id DESC LIMIT 1');
    // $order_id = (int)$result;
    $order = $wpdb->get_var("SELECT product_id FROM wp_wc_order_product_lookup WHERE order_id = $order_id");
    $valid_until = $wpdb->get_var("SELECT subscribtion_valid_until FROM wp_users WHERE ID = $userID");
    $order_status = $wpdb->get_var("SELECT verified FROM wp_wc_order_product_lookup WHERE order_id = $order_id");
    $date = date("Y\-m\-d");
    if ($order_id != 0) {
        if ($order_status != "1") {
            if ($order == 649) {
                $membership = 1;
                $membershipStr = "Silber";
                if ($valid_until > $date) {
                    $valid_until_new = date('Y-m-d', strtotime($valid_until . ' + 6 months'));
                } elseif ($valid_until < $date) {
                    $valid_until_new = date('Y-m-d', strtotime($date . ' + 6 months'));
                }
            } elseif ($order == 650) {
                $membership = 2;
                $membershipStr = "Gold";
                if ($valid_until > $date) {
                    $valid_until_new = date('Y-m-d', strtotime($valid_until . ' + 1 years'));
                } elseif ($valid_until < $date) {
                    $valid_until_new = date('Y-m-d', strtotime($date . ' + 1 years'));
                }
            }
            if ($order == 649 or $order == 650) {
                $table = 'wp_users';
                $data = array('membership' => $membership);
                $where = array('ID' => $userID);
                $wpdb->update($table, $data, $where);
                $data = array('subscribtion_valid_until' => $valid_until_new);
                $wpdb->update($table, $data, $where);

                $table = 'wp_wc_order_product_lookup';
                $data = array('verified' => '1');
                $where = array('order_id' => $order_id);
                $wpdb->update($table, $data, $where);
            }
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
<p>Thank you for your purchase!</p>
<p>Explore our courses timetable <a href="/courses">here!</a></p>
<p>You bought the $membershipStr membership and paid with</p>
<p>$payment_method!</p>
</div>
EOL;
