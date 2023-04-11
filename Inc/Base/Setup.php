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

class Setup extends BaseController
{

    function __construct()
    {
    }
    public function register()
    {
        global $wpdb;
        global $wp_query;
        if (!isset($wp_query)) {
            return;
        }
        $page_paths = [
            'addtocart',
            'Courses',
            'Course',
            'Thanks',
            'Videos'
        ];
        $snippets = [
            'addtocart',
            'Courses',
            'Course',
            'Verify',
            'deleteExpCourse',
            'Videos',
            'updateCourses',
            'registerButton'
        ];
        $global_snippets = [
            'accountTab'
        ];

        foreach ($global_snippets as $snippet) {
            $this->createGlobalSnippet($snippet);
        }
        foreach ($snippets as $snippet) {
            $this->createSnippets($snippet);
        }
        foreach ($page_paths as $page_path) {

            $page = get_page_by_path($page_path);

            if (!$page) {
                $this->create_page($page_path);
            }
        }
        $this->create_table();
        $testTableName = $wpdb->prefix . "users";

        $this->createDatabaseColumn("$wpdb->prefix" . "users", "subscription_valid_until", "date", "display_name", "'0000-00-00'");
        $this->createDatabaseColumn("$wpdb->prefix" . "users", "registered_courses", "text", "display_name", "'0'");
        $this->createDatabaseColumn("$wpdb->prefix" . "users", "membership", "tinyint(11)", "display_name", 0);
        $this->createDatabaseColumn("$wpdb->prefix" . "my_calendar", "courseID", "tinyint(11)", "event_end", 0);
    }
    function create_page($name)
    {
        global $wpdb;
        $title = "";
        switch ($name) {
            case "Courses":
                $content = '[xyz-ips snippet="Courses"] [xyz-ips snippet="deleteExpCourse"]';
                $title = "Kurse";
                break;
            case "Thanks":
                $content = '[xyz-ips snippet="Verify"] [xyz-ips snippet="updateCourses"]';
                $title = "Danke";
                break;
            case "addtocart":
                $content = '[xyz-ips snippet="addtocart"]';
                $title = "addtocart";
                break;
            case "Course":
                $content = '[xyz-ips snippet="Course"] [xyz-ips snippet="updateCourses"]';
                $title = "Kurs";
                break;
            case "Videos":
                $content = '[xyz-ips snippet="Videos"] [xyz-ips snippet="updateCourses"]';
                $title = "Videos";
                break;
        }
        $page = [
            'post_title'  => __($title),
            'post_name' => $name,
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
  id mediumint(9) NOT NULL UNIQUE AUTO_INCREMENT, 
  course_name text NOT NULL,
  price int NOT NULL,
  date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  repeat_every text NOT NULL,
  description text NOT NULL,
  url varchar(55) DEFAULT '' NOT NULL,
  product_id int NOT NULL UNIQUE,
  registrations int NOT NULL DEFAULT 0,
  max_registrations int DEFAULT null,
  registered_emails text not null DEFAULT '',
  event_id int NOT NULL UNIQUE,
  PRIMARY KEY  (id)
) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        $table_name = $wpdb->prefix . "courseVideos";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL UNIQUE AUTO_INCREMENT, 
            file_path text NOT NULL,
            file_url text NOT NULL,
            video_name text NOT NULL,
            video_description text NOT NULL,
            PRIMARY KEY  (id)
          ) $charset_collate;";
        dbDelta($sql);
        $table_name = $wpdb->prefix . "courseOrders";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL UNIQUE AUTO_INCREMENT,
            order_id int NOT NULL DEFAULT 0,
            completed boolean NOT NULL DEFAULT false,
            user_id int NOT NULL DEFAULT 0,
            product_id int NOT NULL DEFAULT 0,
            order_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            refund boolean NOT NULL DEFAULT false,
            PRIMARY KEY (id)
            ) $charset_collate;";
        dbDelta($sql);
        $table_name = $wpdb->prefix . "courseSettings";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL UNIQUE AUTO_INCREMENT,
            membership_productID int NOT NULL UNIQUE DEFAULT 0,
            membership_type text NOT NULL DEFAULT '',
            PRIMARY KEY (id)
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
        $table_name = $wpdb->prefix . "xyz_ips_short_code";
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

    function createGlobalSnippet($snippet)
    {
        global $wpdb;
        $datetime = date('Y-m-d H:i:s');
        $table_name = $wpdb->prefix . "snippets";
        $code = file_get_contents('snippets/' . strtolower($snippet) . '.php', "r");
        $code = str_replace("<?php", "", $code);
        $wpdb->delete($table_name, array('name' => $snippet));
        $wpdb->insert(
            $table_name,
            array(
                'name' => $snippet,
                'code' => strval($code),
                'scope' => "global",
                'priority' => 10,
                'active' => 1,
                'modified' => $datetime,
            )
        );
    }
}
