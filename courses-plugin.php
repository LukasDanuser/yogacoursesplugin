<?php

/**
 * @package CoursesPlugin
 */

/*
Plugin Name: Courses Plugin
Description: Create and sell online courses.
Version: 1.1.3
Author: Lukas Danuser
License: GPLv3 or later
Text Domain: courses-plugin
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

// If this file is called firectly, abort!!!
defined('ABSPATH') or die('Hey, what are you doing here?');

// Require once the Composer Autoload
if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}

/**
 * The code that runs during plugin activation
 */

function activate_courses_plugin()
{
    Inc\Base\Activate::activate();
}

/**
 * The code that runs during plugin deactivation
 */

function deactivate_courses_plugin()
{
    Inc\Base\Deactivate::deactivate();
}

register_activation_hook(__FILE__, 'activate_courses_plugin');
register_deactivation_hook(__FILE__, 'deactivate_courses_plugin');


if (class_exists('Inc\\Init')) {
    Inc\Init::register_services();
}

// Include our updater file
include_once(plugin_dir_path(__FILE__) . 'updater.php');

$updater = new Courses_Updater(__FILE__); // instantiate our class
$updater->set_username('LukasDanuser'); // set username
$updater->set_repository('coursesplugin'); // set repo
$updater->initialize();
