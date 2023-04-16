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
$date = date('Y-m-d H:i:s');
$results = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courses");
$deleted = false;

$table = $wpdb->prefix . 'courseOccur';
$data = array('verified' => 2);
$where = array('verified' => 0);
$wpdb->update($table, $data, $where);


$courses = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courses");
//get all users
$users = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "users");
foreach ($courses as $course) {
    if ($course->date < $date) {
        $newCourses = "";
        foreach ($users as $user) {
            $registered_courses = $user->registered_courses;
            if (str_contains($registered_courses, ';' . $course->id . ';') and $course->date < $date) {
                $newCourses = str_replace($course->id . ';', '', $registered_courses);
                $newCourses = $newCourses == ';' ? '0' : $newCourses;
                $table = $wpdb->prefix . 'users';
                $data = array('registered_courses' => $newCourses);
                $where = array('ID' => $user->ID);
                $wpdb->update($table, $data, $where);
            }
        }
    }
    if ($course->date < $date) {
        $newEmails = "";
        $newRegs = 0;
        $table = $wpdb->prefix . 'courses';
        $data = array('registered_emails' => $newEmails);
        $where = array('id' => $course->id);
        $wpdb->update($table, $data, $where);
        $data = array('registrations' => $newRegs);
        $wpdb->update($table, $data, $where);
    }
}

foreach ($results as $course) {
    $courseDate = $wpdb->get_var("SELECT date FROM $wpdb->prefix" . "courses WHERE id = $course->id");
    if ($date > $course->date) {
        if ($course->repeat_every != "never") {
            while ($date > $courseDate) {
                $courseDate = $wpdb->get_var("SELECT date FROM $wpdb->prefix" . "courses WHERE id = $course->id");
                $newCourseDate = date('Y-m-d H:i:s', $courseDate);
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
                $courseDate = $wpdb->get_var("SELECT date FROM $wpdb->prefix" . "courses WHERE id = $course->id");
            }
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
