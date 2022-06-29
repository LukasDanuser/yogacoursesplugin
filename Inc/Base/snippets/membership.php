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
$href = "";
$gold = $wpdb->get_var("SELECT membership_productID FROM $wpdb->prefix" . "courseSettings WHERE membership_type = 'annual'");
$silver = $wpdb->get_var("SELECT membership_productID FROM $wpdb->prefix" . "courseSettings WHERE membership_type = 'semiannual'");

if (is_user_logged_in()) {
    $href = "checkout";
} else {
    $href = "register";
}
$link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[SERVER_NAME]";

?>
<div id="membership" style="
display: grid;
grid-template-columns: repeat(2, 1fr);
grid-gap: 10px;
grid-auto-rows: minmax(100px, auto);">
    <a style="text-align: center;" id="silver" href="/addtocart?id=<?php echo $silver ?>&href=<?php echo $href; ?>"><img style="display: block;
  margin-left: auto;
  margin-right: auto;" alt="silver" src="<?php echo $link; ?>/wp-content/plugins/coursesplugin/images/silver-membership.png" width="100" height="100">
        <p>Halbes Jahr</p>
    </a>

    <a style="text-align: center;" id="gold" href="/addtocart?id=<?php echo $gold ?>&amp;href=<?php echo $href; ?>"><img style="display: block;
  margin-left: auto;
  margin-right: auto;" alt="gold" src="<?php echo $link; ?>/wp-content/plugins/coursesplugin/images/gold-membership.png" width="100" height="100">
        <p>Jahr</p>
    </a>
</div>