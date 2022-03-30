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

if (isset($_GET['order'])) {
    $orderID = $_GET['order'];
    if ($orderID == "649") {
        echo "silver";
    } elseif ($orderID == "650") {
        echo "gold";
    }
}
echo "<td colspan='2'><hr size='1'></td>";
echo "<tr>";
echo "<th>" . $count . "</th>";
echo "</tr>";

echo "<td colspan='2'><hr size='1'></td>";
echo "<tr>";
echo "<th>Kurs Name</th>" . "<td>" . $row->course_name . "</td>";
echo "</tr>";

echo "<td colspan='2'><hr size='1'></td>";
echo "<tr>";
echo "<th>Teilnehmer</th>" . "<td>" . $row->registrations . "</td>";
echo "</tr>";

echo "<td colspan='2'><hr size='1'></td>";
echo "<tr>";
echo "<th>Preis</th>" . "<td>" . $row->price . "</td>";
echo "</tr>";

echo "<td colspan='2'><hr size='1'></td>";
echo "<tr>";
echo "<th>Datum</th>" . "<td>" . $courseDate . "</td>";
echo "</tr>";

echo "<td colspan='2'><hr size='1'></td>";
echo "<tr>";
echo "<th>Beschreibung</th>" . "<td>" . $row->description . "</td>";
echo "</tr>";

echo "<td colspan='2'><hr size='1'></td>";
echo "<tr>";
echo "<th>Link</th>" . "<td>" . $row->url . "</td>";
echo "</tr>";

echo "<td colspan='2'><hr size='1'></td>";
echo "<tr>";
echo "<th>Produkt ID</th>" . "<td>" . $row->product_id . "</td>";
echo "</tr>";

echo "<td colspan='2'><hr size='1'></td>";
echo "<tr>";
echo "<th><a href=$actual_link&delete=$id>Löschen</a></th>";
echo "</tr>";

echo "<td colspan='2'><hr size='1'></td>";
echo "<tr>";
echo "<th><br></th>";
echo "</tr>";
