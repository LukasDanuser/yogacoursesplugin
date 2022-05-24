<?php
global $wpdb;
$membership = 0;
$results = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courses ORDER BY date ASC");
$count = 0;
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$elementIDs = $wpdb->get_results("SELECT id FROM $wpdb->prefix" . "courses");
$repeatTemp = "";
if (!empty($results)) {
}
