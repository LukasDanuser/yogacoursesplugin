<?php

/**
 * @package CoursesPlugin
 */

namespace Inc\Pages;

use \Inc\Base\BaseController;

class Admin extends BaseController
{
    function __construct()
    {
    }

    public function register()
    {
        add_action('admin_menu', array($this, 'add_admin_pages'));
    }

    public function add_admin_pages()
    {
        add_menu_page('Courses Plugin', 'Courses', 'manage_options', 'courses_plugin', array($this, 'admin_index'), '', '110');
        add_submenu_page('courses_plugin', 'Courses Plugin', '', 'manage_options', 'upload_file', array($this, 'admin_upload_index'), '', '110');
    }

    public function admin_upload_index()
    {
        require_once plugin_dir_path(dirname(__FILE__, 2)) . 'templates/upload_file.php';
    }

    public function admin_index()
    {
        require_once plugin_dir_path(dirname(__FILE__, 2)) . 'templates/admin.php';
    }
}
