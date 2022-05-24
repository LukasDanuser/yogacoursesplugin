<h1>Courses Plugin</h1>
<?php
/**
 * @package CoursesPlugin
 */

/*https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous
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


$course_name = isset($_REQUEST['course_name']) ? $_REQUEST['course_name'] : "";
$price = isset($_REQUEST['price']) ? $_REQUEST['price'] : "";
$description = isset($_REQUEST['description']) ? $_REQUEST['description'] : "";
$link = isset($_REQUEST['link']) ? $_REQUEST['link'] : "";
$date = isset($_REQUEST['date']) ? $_REQUEST['date'] : "";
$repeat = isset($_POST['repeat']) ? $_POST['repeat'] : "";
$submit = isset($_REQUEST['submit']) ? "submitted" : "";
$delete = isset($_REQUEST['delete']) ? "delete" : "";
$deleteVid = isset($_REQUEST['deleteVid']) ? 'deleteVid' : "";
global $wpdb;


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
if ($deleteVid == "deleteVid") {
    $id = isset($_REQUEST['deleteVid']) ? $_REQUEST['deleteVid'] : 0;
    $video = $wpdb->get_row("SELECT * FROM $wpdb->prefix" . "courseVideos" . " WHERE id = $id");
    $table = "$wpdb->prefix" . "courseVideos";
    wp_delete_file($video->file_path);
    $wpdb->delete($table, array('id' => $id));
?><script>
        window.location.href = "/wp-admin/admin.php?page=courses_plugin";
    </script><?php
            }
            if ($delete == "delete") {


                $id = isset($_REQUEST['delete']) ? $_REQUEST['delete'] : 0;
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
                $product_id = createProduct($course_name, $description, $price, null);
                $table_name = "$wpdb->prefix" . "courses";
                $wpdb->insert(
                    $table_name,
                    array(
                        'course_name' => $course_name,
                        'price' => $price,
                        'date' => $date,
                        'repeat_every' => $repeat,
                        'description' => $description,
                        'url' => $link,
                        'product_id' => $product_id
                    )
                );
            }
                ?>
<form method="post">
    <label for="course_name">Name</label>
    <input type="text" name="course_name" id="course_name" required><br><br>
    <label for="price">Preis</label>
    <input type="number" name="price" id="price" required><br><br>
    <label for="date">Datum</label>
    <input type="datetime-local" name="date" id="date" required><br><br>
    <label for="repeat">Wiederholen</label>
    <select name="repeat" id="repeat" required>
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
    <label for="description">Beschreibung</label>
    <input type="text" name="description" id="description" required><br><br>
    <label for="link">Link</label>
    <input type="text" name="link" id="link" required><br><br>
    <input type="submit" name="submit" value="submit">
</form>
<form action="admin.php?page=upload_file" method="post" enctype="multipart/form-data">
    <label for="file"><span>Filename:</span></label>
    <input type="file" name="file" id="file" /><br><br>
    <label for="vidName"></label>
    <input type="text" name="vidName" id="vidName" placeholder="Name des Videos" required><br><br>
    <label for="vidDes"></label>
    <input type="text" name="vidDes" id="vidDes" placeholder="Beschreibung" required>
    <br /><br>
    <input type="submit" name="submit" value="Submit" />
</form>
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
        echo "
        <div class=\"course\" style=\"grid-column: $t1Course; grid-row: $t2Course;\">
            <div id=\"course$row->id\" class=\"course-card\">
                <div class=\"card-body\">
                    <h5 class=\"card-title text-center\"><b><u>
                                <p>$row->course_name</p>
                            </u></b></h5>
                    <p class=\"card-text\">$row->description</p>
                    <p><b>Teilnehmer: </b> $row->registrations</p>
                    <p><b>Datum:</b> $courseDate</p>
                    <p><b>Wiederholung:</b> $repeatTemp</p>
                    <p><b>Link:</b><a href=\"$row->url\"> $row->url</a></p>
                    <p><b>Produkt ID:</b> $row->product_id</p>
                    <div class=\"text-center\">
                        <label class=\"control-label\" >
                            <p>CHF $row->price</p>
                        </label>
                    </div>
                    <div class=\" Löschen text-center\">
                        <button onclick=\"window.location.href='$actual_link&delete=$row->id';\">Löschen</button>
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
      <div id=\"video$row->id\" class=\"video-card\">
          <div class=\"video-body\">
              <h5 class=\"video-title text-center\"><b><u>
                          <p>$video->video_name</p>
                      </u></b></h5>
              <p class=\"video-text\">$video->video_description</p>
              <video width=\"320\" height=\"240\" controls>
        <source src=\"$video->file_url\" type=\"video/mp4\">
        Your browser does not support the video tag.
        </video> <br><br>
              <div class=\" Löschen text-center\">
                  <button onclick=\"window.location.href='$actual_link&deleteVid=$video->id';\">Löschen</button>
              </div>
          </div>
      </div>
</div>";
    }
    echo "</div>";
}
