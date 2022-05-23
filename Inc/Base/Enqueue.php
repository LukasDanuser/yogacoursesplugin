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

class Enqueue extends BaseController
{
    function __construct()
    {
    }

    public function register()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
    }
    function enqueue()
    {
        // enqueue all scripts
        wp_enqueue_style('style', '/wp-content/plugins/coursesplugin/assets/style.css', __FILE__);
        wp_enqueue_script('coursesscript', '/wp-content/plugins/coursesplugin/assets/courses.js', __FILE__);
    }
}
