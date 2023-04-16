<h1>Kurse Admin</h1>
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
$course_name = isset($_REQUEST['course_name']) ? htmlspecialchars($_REQUEST['course_name']) : "";
$price = isset($_REQUEST['price']) ? htmlspecialchars($_REQUEST['price']) : "";
$description = isset($_REQUEST['description']) ? htmlspecialchars($_REQUEST['description']) : "";
$link = isset($_REQUEST['link']) ? htmlspecialchars($_REQUEST['link']) : "";
$repeat = isset($_POST['repeat']) ? htmlspecialchars($_POST['repeat']) : "";
$maxReg = isset($_REQUEST['maxReg']) ? htmlspecialchars($_REQUEST['maxReg']) : null;
$submit = isset($_REQUEST['submit']) ? "submitted" : "";
$delete = isset($_REQUEST['delete']) ? "delete" : "";
$deleteVid = isset($_REQUEST['deleteVid']) ? 'deleteVid' : "";
$editCourse = isset($_REQUEST['edit']) ? htmlspecialchars($_REQUEST['edit']) : "";
$eventID = isset($_REQUEST['eventID']) ? htmlspecialchars($_REQUEST['eventID']) : 0;
$course_nameValue = "";
$priceValue = "";
$descriptionValue = "";
$eventIDValue = "";
$linkValue = "";
$repeatValue = "";
$mmIDSet = $wpdb->get_var("SELECT membership_productID FROM $wpdb->prefix" . "courseSettings WHERE membership_type = 'annual'");
$current_datetime = date('Y-m-d H:i:s');
$event_next_occur = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "my_calendar_events WHERE occur_event_id = $eventID");
$date = "";
$event_next_occur_id = "";

foreach ($event_next_occur as $event) {
    if ($event->occur_begin > $current_datetime) {
        $date = $event->occur_begin;
        $event_next_occur_id = $event->occur_id;
        break;
    }
}

function createProduct($title, $body, $price, $sku)
{
    $post_id = wp_insert_post(array(
        'post_title' => $title,
        'post_type' => 'product',
        'post_status' => 'publish',
        'post_content' => $body,
    ));
    $product = wc_get_product($post_id);
    $product->set_sku($sku);
    $product->set_price($price);
    $product->set_regular_price($price);
    $product->save();

    return $post_id;
}

if ($editCourse != "" || $editCourse != null) {
    $results = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courses WHERE id = $editCourse");
    foreach ($results as $result) {
        switch ($result->repeat_every) {
            case "day":
                $repeatTemp = "Täglich";
                break;
            case "week":
                $repeatTemp = "Wöchentlich";
                break;
            case "month":
                $repeatTemp = "Monatlich";
                break;
            case "2month":
                $repeatTemp = "Alle 2 Monate";
                break;
            case "3month":
                $repeatTemp = "Alle 3 Monate";
                break;
            case "4month":
                $repeatTemp = "Alle 4 Monate";
                break;
            case "5month":
                $repeatTemp = "Alle 5 Monate";
                break;
            case "6month":
                $repeatTemp = "Alle 6 Monate";
                break;
            case "never":
                $repeatTemp = "Nie";
                break;
        }
        $course_nameValue = $result->course_name;
        $priceValue = $result->price;
        $descriptionValue = $result->description;
        $linkValue = $result->url;
        $repeatValue = $result->repeat_every;
        $maxRegValue = $result->max_registrations;
        $eventIDValue = $result->event_id;
    }
}

if ($deleteVid == "deleteVid") {
    $id = isset($_REQUEST['deleteVid']) ? htmlspecialchars($_REQUEST['deleteVid']) : 0;
    $video = $wpdb->get_row("SELECT * FROM $wpdb->prefix" . "courseVideos" . " WHERE id = $id");
    $table = "$wpdb->prefix" . "courseVideos";
    wp_delete_file($video->file_path);
    $wpdb->delete($table, array('id' => $id));
?><script>
        window.location.href = "/wp-admin/admin.php?page=courses_plugin";
    </script><?php
            }
            if ($delete == "delete") {
                $id = isset($_REQUEST['delete']) ? htmlspecialchars($_REQUEST['delete']) : 0;
                $result = $wpdb->get_var("SELECT product_id FROM $wpdb->prefix" . "courses WHERE id = $id");
                $table = "$wpdb->prefix" . "wc_product_meta_lookup";
                $wpdb->delete($table, array('product_id' => $result));
                $table = "$wpdb->prefix" . "posts";
                $wpdb->delete($table, array('ID' => $result));
                $table = "$wpdb->prefix" . "courses";
                $wpdb->delete($table, array('id' => $id));

                ?><script>
        window.location.href = "/wp-admin/admin.php?page=courses_plugin";
    </script><?php
            }

            if ($submit == "submitted") {
                if ($editCourse != "" || $editCourse != null) {
                    $table = $wpdb->prefix . 'courses';
                    $maxReg = $maxReg == 0 ? null : ($maxReg == "" ? null : $maxReg);
                    $data = array(
                        'course_name' => $course_name,
                        'price' => $price,
                        'date' => $date,
                        'repeat_every' => $repeat,
                        'description' => $description,
                        'url' => $link,
                        'max_registrations' => $maxReg,
                        'event_id' => $eventID
                    );
                    $where = array('id' => $editCourse);
                    $wpdb->update($table, $data, $where);
                    $_POST = array();
                ?><script>
            window.location.href = "/wp-admin/admin.php?page=courses_plugin";
        </script><?php
                } else {
                    $product_id = createProduct($course_name, $description, $price, null);
                    $table_name = "$wpdb->prefix" . "courses";
                    $maxReg = $maxReg == 0 ? null : ($maxReg == "" ? null : $maxReg);
                    $wpdb->insert(
                        $table_name,
                        array(
                            'course_name' => $course_name,
                            'price' => $price,
                            'date' => $date,
                            'repeat_every' => $repeat,
                            'description' => $description,
                            'url' => $link,
                            'product_id' => $product_id,
                            'max_registrations' => $maxReg,
                            'event_id' => $eventID,
                        )
                    );
                    $_POST = array();
                }
            }
            if ($mmIDSet == null or $mmIDSet == "") {
                if (isset($_REQUEST['submitID'])) {
                    $wpdb->insert(
                        "$wpdb->prefix" . "courseSettings",
                        array(
                            'membership_productID' => $_REQUEST['annual_productID'],
                            'membership_type' => 'annual'
                        )
                    );
                    $wpdb->insert(
                        "$wpdb->prefix" . "courseSettings",
                        array(
                            'membership_productID' => $_REQUEST['semiAnnual_productID'],
                            'membership_type' => 'semiannual'
                        )
                    );
                    header("Refresh:0");
                }
                echo <<< EOL
                <form method="post">
                <p>Bitte setzen Sie die IDs der Produkte für die Mitgliedschaften:</p>
                <input type="number" name="annual_productID" placeholder="Product ID jährlich" id="annual_productID" required>
                <input type="number" name="semiAnnual_productID" placeholder="Product ID halb-jährlich" id="semiAnnual_productID" required>
                <input type="submit" name="submitID" value="Speichern" /><br>
                </form><br>
                EOL;
            }


                    ?>
<form method="post">
    <input type="text" name="course_name" id="course_name" placeholder="Kurs Name" value="<?php echo $course_nameValue; ?>" required><br><br>
    <input type="number" name="price" id="price" placeholder="Preis" value="<?php echo $priceValue; ?>" required><br><br>
    <label for="repeat">Wiederholen</label>
    <select name="repeat" id="repeat" required>
        <?php

        if ($repeatValue != "" || $repeatValue != null) {
            echo '<option value="' . $repeatValue . '" selected>' . $repeatTemp . '</option>';
        }

        ?>
        <option value="day">Täglich</option>
        <option value="week">Wöchentlich</option>
        <option value="month">Monatlich</option>
        <option value="2month">Alle 2 Monate</option>
        <option value="3month">Alle 3 Monate</option>
        <option value="4month">Alle 4 Monate</option>
        <option value="5month">Alle 5 Monate</option>
        <option value="6month">Alle 6 Monate</option>
        <option value="never">Nie</option>
    </select><br><br>
    <input type="text" name="description" id="description" placeholder="Beschreibung" value="<?php echo $descriptionValue; ?>" required><br><br>
    <input type="number" name="maxReg" id="maxReg" placeholder="Maximale Anmeldungen" title="Leer lassen für unlimitiert" value="<?php echo $maxRegValue; ?>"><br><br>
    <input type="text" name="link" id="link" placeholder="Link" value="<?php echo $linkValue; ?>" required><br><br>
    <input type="number" name="eventID" id="eventID" placeholder="Event ID" value="<?php echo $eventIDValue; ?>" required><br><br>
    <input type="submit" name="submit" value="Speichern">
</form>
<form action="admin.php?page=upload_file" method="post" enctype="multipart/form-data">
    <label for="file"><span>Filename:</span></label>
    <input type="file" name="file" id="file" /><br><br>
    <label for="vidName"></label>
    <input type="text" name="vidName" id="vidName" placeholder="Name des Videos" required><br><br>
    <label for="vidDes"></label>
    <input type="text" name="vidDes" id="vidDes" placeholder="Beschreibung" required>
    <br /><br>
    <input type="submit" name="submit" value="Speichern" /><br>
</form>
<br>
<?php
$results = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courses ORDER BY date ASC");
$videos = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "courseVideos");
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

if (!empty($results)) {
    echo "<div class=\"courses\" style=\"display: grid; grid-template-columns: repeat(5, minmax(20em, 1fr)); grid-gap: 1rem; grid-template-rows: repeat(auto-fill, minmax(20em, 1fr));\">";
    $elementIDs = $wpdb->get_results("SELECT id FROM $wpdb->prefix" . "courses");
    $t1Course = 0;
    $t2Course = 1;

    foreach ($results as $row) {
        $t1Course++;
        if ($t1Course > 5) {
            $t2Course++;
            $t1Course = 1;
        }
        $cDate = new DateTime($row->date);
        $courseDate = $cDate->format('d.m.Y H:i');
        $repeatTemp = "";
        switch ($row->repeat_every) {
            case "day":
                $repeatTemp = "Täglich";
                break;
            case "week":
                $repeatTemp = "Wöchentlich";
                break;
            case "month":
                $repeatTemp = "Monatlich";
                break;
            case "2month":
                $repeatTemp = "Alle 2 Monate";
                break;
            case "3month":
                $repeatTemp = "Alle 3 Monate";
                break;
            case "4month":
                $repeatTemp = "Alle 4 Monate";
                break;
            case "5month":
                $repeatTemp = "Alle 5 Monate";
                break;
            case "6month":
                $repeatTemp = "Alle 6 Monate";
                break;
            case "never":
                $repeatTemp = "Nie";
                break;
        }
        $maxRegistrations = "";
        if ($row->max_registrations == null) {
            $maxRegistrations = "Unlimitiert";
        } else {
            $maxRegistrations = $row->max_registrations;
        }
        echo "
        <div class=\"course\" style=\"grid-column: $t1Course; grid-row: $t2Course;\">
            <div id=\"course$row->id\" class=\"course-card\">
                <div class=\"card-body\">
                    <h5 class=\"card-title text-center\"><b><u>
                                <p>$row->course_name</p>
                            </u></b></h5>
                    <p><b>Kurs ID: </b> $row->id</p>
                    <p class=\"card-text\"><b>Beschreibung: </b>$row->description</p>
                    <p><b>Teilnehmer: </b> $row->registrations</p>
                    <p><b>Datum:</b> $courseDate</p>
                    <p><b>Wiederholung:</b> $repeatTemp</p>
                    <p><b>Max Anmeldungen:</b> $maxRegistrations</p>
                    <p><b>Link:</b><a href=\"$row->url\"> $row->url</a></p>
                    <p><b>Produkt ID:</b> $row->product_id</p>
                    <p><b>Event ID:</b> $row->event_id</p>
                    <div class=\"text-center\">
                        <label class=\"control-label\" >
                            <p>CHF $row->price</p>
                        </label>
                    </div>
                    <div class=\"buttons text-center\">
                        <button onclick=\"window.location.href='$actual_link&delete=$row->id';\">Löschen</button>
                        <button onclick=\"window.location.href='$actual_link&edit=$row->id';\">Bearbeiten</button>
                    </div>
                </div>
            </div>
        </div>
        ";
    }
    echo "</div><br>";
}
if (!empty($videos)) {
    $t1Video = 0;
    $t2Video = 1;
    echo "<div class=\"videos\" style=\"display: grid; grid-template-columns: repeat(5, minmax(20em, 1fr)); grid-gap: 1rem; grid-template-rows: repeat(auto-fill, minmax(20em, 1fr));\">";
    foreach ($videos as $video) {
        $t1Video++;
        if ($t1Video > 5) {
            $t2Video++;
            $t1Video = 1;
        }
        echo "
      <div class=\"video\" style=\"grid-column: $t1Video; grid-row: $t2Video;\">
      <div id=\"video$video->id\" class=\"video-card\">
          <div class=\"video-body\">
              <h5 class=\"video-title text-center\"><b><u>
                          <p>$video->video_name</p>
                      </u></b></h5>
              <p class=\"video-text\">$video->video_description</p>
              <video width=\"320\" height=\"240\" controls>
        <source src=\"$video->file_url\" type=\"video/mp4\">
        Your browser does not support the video tag.
        </video> <br><br>
              <div class=\"Löschen text-center\">
                  <button onclick=\"window.location.href='$actual_link&deleteVid=$video->id';\">Löschen</button>
              </div>
          </div>
      </div>
</div>";
    }
    echo "</div>";
}
