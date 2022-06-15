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
//get all courses
$courses = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courses");
//get user from database
if (is_user_logged_in()) {
    $newCourses = "";
    $newEmails = "";
    $user = $wpdb->get_row("SELECT * FROM $wpdb->prefix" . "users WHERE ID = " . get_current_user_id());
    $registered_courses = $user->registered_courses;
    $date = date('Y-m-d H:i:s');
    foreach ($courses as $course) {
        $registered_emails = $course->registered_emails;
        if ((str_contains($registered_courses, ';' . $course->id . ';') or str_contains($registered_emails, ';' . wp_get_current_user()->user_email . ';')) and $course->date < $date) {
            $newCourses = str_replace(';' . $course->id . ';', '', $registered_courses);
            $table = $wpdb->prefix . 'user';
            $data = array('registered_courses' => $newCourses);
            $where = array('ID' => get_current_user_id());
            $wpdb->update($table, $data, $where);
        }
    }
}
