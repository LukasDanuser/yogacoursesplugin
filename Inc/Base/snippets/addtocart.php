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

if (isset($_GET['id'])) {
    global $woocommerce;
    WC()->cart->empty_cart();
    $woocommerce->cart->add_to_cart($_GET['id']);

    if (isset($_GET['href'])) {
        $href = $_GET['href'];
        if ($href == "register") {
?> <script>
                window.location.href = "/register";
            </script><?php
                        exit;
                    } elseif ($href == "checkout") {
                        ?> <script>
                window.location.href = "/checkout";
            </script><?php
                        exit;
                    }
                }
            } else {
                        ?> <script>
        window.location.href = "/";
    </script><?php
                exit;
            }
