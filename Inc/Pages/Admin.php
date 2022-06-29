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
        $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[SERVER_NAME]";
        add_menu_page('Kurse Admin', 'Kurse', 'manage_options', 'courses_plugin', array($this, 'admin_index'), 'dashicons-welcome-learn-more', '58');
        add_submenu_page(null, 'Courses Plugin', null, 'manage_options', 'upload_file', array($this, 'admin_upload_index'), '59');
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
