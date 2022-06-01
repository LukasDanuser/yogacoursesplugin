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

namespace Inc\Base;

use \Inc\Base\BaseController;

class PageCreator extends BaseController
{
    function __construct()
    {
    }
    public function register()
    {
        global $wp_query;
        if (!isset($wp_query)) {
            return;
        }
        $page_paths = [
            'addtocart',
            'Courses',
            'Course',
            'Membership',
            'Thanks',
            'Videos'
        ];
        $snippets = [
            'addtocart',
            'Courses',
            'Course',
            'Membership',
            'Thanks',
            'Verify',
            'deleteExpCourse',
            'videos'
        ];

        foreach ($page_paths as $page_path) {

            $page = get_page_by_path($page_path);

            if (!$page) {
                $this->create_page($page_path);
            }
        }
        foreach ($snippets as $snippet) {
            global $wpdb;
            $this->createSnippets($snippet);
        }
        global $wpdb;
        $this->create_table();
        $testTableName = $wpdb->prefix . "users";

        $this->createDatabaseColumn("$wpdb->prefix" . "users", "subscription_valid_until", "date", "display_name", "'0000-00-00'");
        $this->createDatabaseColumn("$wpdb->prefix" . "users", "registered_courses", "tinyint(11)", "display_name", 0);
        $this->createDatabaseColumn("$wpdb->prefix" . "users", "membership", "tinyint(11)", "display_name", 0);
        $this->createDatabaseColumn("$wpdb->prefix" . "wc_order_product_lookup", "verified", "tinyint(11)", "shipping_tax_amount", 0);
    }
    function create_page($name)
    {
        switch ($name) {
            case "Membership":
                $content = '[xyz-ips snippet="Membership"]';
                break;
            case "Courses":
                $content = '[xyz-ips snippet="Courses"] [xyz-ips snippet="deleteExpCourse"]';
                break;
            case "Thanks":
                $content = '[xyz-ips snippet="Thanks"]';
                break;
            case "addtocart":
                $content = '[xyz-ips snippet="addtocart"]';
                break;
            case "Course":
                $content = '[xyz-ips snippet="Course"]';
                break;
            case "Videos":
                $content = '[xyz-ips snippet="Videos"]';
                break;
        }
        $page = [
            'post_title'  => __($name),
            'post_name' => '',
            'post_status' => 'publish',
            'post_content' => $content,
            'post_author' => 1,
            'post_type'   => 'page',
        ];

        // insert the post into the database
        wp_insert_post($page);
    }

    function create_table()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . "courses";

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
  id mediumint(9) NOT NULL AUTO_INCREMENT, 
  course_name text NOT NULL,
  price int NOT NULL,
  date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  repeat_every text NOT NULL,
  description text NOT NULL,
  url varchar(55) DEFAULT '' NOT NULL,
  product_id int NOT NULL UNIQUE,
  registrations int NOT NULL DEFAULT 0,
  PRIMARY KEY  (id)
) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        $table_name = $wpdb->prefix . "courseVideos";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT, 
            file_path text NOT NULL,
            file_url text NOT NULL,
            video_name text NOT NULL,
            video_description text NOT NULL,
            PRIMARY KEY  (id)
          ) $charset_collate;";
        dbDelta($sql);
    }

    function createDatabaseColumn($table, $column, $datatype, $after, $default)
    {
        global $wpdb;

        $row = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE '$column'");
        if (empty($row) || $row == null) {
            $sql = "ALTER TABLE $table ADD $column $datatype DEFAULT $default NOT NULL after $after";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $wpdb->query($sql);
        }
    }

    function createSnippets($name)
    {
        global $wpdb;
        $code = file_get_contents('snippets/' . strtolower($name) . '.php', "r");
        $table_name = "$wpdb->prefix" . "xyz_ips_short_code";
        $wpdb->delete($table_name, array('title' => $name));
        $wpdb->insert(
            $table_name,
            array(
                'title' => $name,
                'content' => strval($code),
                'short_code' => "[xyz-ips snippet=\"$name\"]",
                'status' => 1
            )
        );
    }
}
