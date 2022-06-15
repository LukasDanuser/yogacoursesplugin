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
//get all courses
$courses = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courses");
//get all users
$users = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "users");
foreach ($courses as $course) {
    if ($course->date < $date) {
        $newCourses = "";
        foreach ($users as $user) {
            $registered_courses = $user->registered_courses;
            if (str_contains($registered_courses, ';' . $course->id . ';') and $course->date < $date) {
                $newCourses = str_replace($course->id . ';', '0', $registered_courses);
                $newCourses = $newCourses == ';' ? '' : $newCourses;
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
