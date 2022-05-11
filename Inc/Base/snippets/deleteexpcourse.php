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
$date = date("Y-m-d");
$results = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courses");
$deleted = false;

foreach ($results as $course) {
    if ($date > $course->date) {
        if ($course->repeat_every != "never") {
            $courseDate = $course->date;
            $newCourseDate = date('Y-m-d H:i:s', strtotime($courseDate . ' + 1 days'));
            switch ($course->repeat_every) {
                case "day":
                    $newCourseDate = date('Y-m-d H:i:s', strtotime($courseDate . ' + 1 days'));
                    break;
                case "week":
                    $newCourseDate = date('Y-m-d H:i:s', strtotime($courseDate . ' + 1 weeks'));
                    break;
                case "month":
                    $newCourseDate = date('Y-m-d H:i:s', strtotime($courseDate . ' + 1 months'));
                    break;
                case "2month":
                    $newCourseDate = date('Y-m-d H:i:s', strtotime($courseDate . ' + 2 months'));
                    break;
                case "3month":
                    $newCourseDate = date('Y-m-d H:i:s', strtotime($courseDate . ' + 3 months'));
                    break;
                case "4month":
                    $newCourseDate = date('Y-m-d H:i:s', strtotime($courseDate . ' + 4 months'));
                    break;
                case "5month":
                    $newCourseDate = date('Y-m-d H:i:s', strtotime($courseDate . ' + 5 months'));
                    break;
                case "6month":
                    $newCourseDate = date('Y-m-d H:i:s', strtotime($courseDate . ' + 6 months'));
                    break;
            }
            $table = $wpdb->prefix . 'courses';
            $data = array('date' => $newCourseDate);
            $where = array('id' => $course->id);
            $wpdb->update($table, $data, $where);
            header("Refresh:0");
        } else {
            $table = "$wpdb->prefix" . "wc_product_meta_lookup";
            $wpdb->delete($table, array('product_id' => $course->product_id));
            $table = "$wpdb->prefix" . "posts";
            $wpdb->delete($table, array('ID' => $course->product_id));
            $table = "$wpdb->prefix" . "courses";
            $wpdb->delete($table, array('id' => $course->id));
            $deleted = true;
        }
    }
}
if ($deleted == true) {
    header("Refresh:0");
    $deleted = false;
}
