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
                    include_once("../Inc/Base/snippets/temp.php")
                ?><script>
            window.location.href = "/wp-admin/admin.php?page=courses_plugin";
        </script><?php
                } else {

                    $dateFormat = str_replace(' ', 'T', $date);
                    $product_id = createProduct($course_name, $description, $price, null);
                    $table_name = "$wpdb->prefix" . "courses";
                    $maxReg = $maxReg == 0 ? null : ($maxReg == "" ? null : $maxReg);
                    echo $dateFormat;
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://api.zoom.us/v2/users/l.danuser@rafisa.ch/meetings",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => "{\r\n  \"agenda\": \"$course_name\",\r\n  \"start_time\": \"$dateFormat\",\r\n \"timezone\": \"Europe/Zurich\",\r\n \"default_password\": false,\r\n  \"duration\": 60,\r\n \"password\": \"\"\r\n}",
                        CURLOPT_HTTPHEADER => array(
                            "Authorization: Bearer  eyJzdiI6IjAwMDAwMSIsImFsZyI6IkhTNTEyIiwidiI6IjIuMCIsImtpZCI6ImZiNzE4OWUzLTRlMzYtNDEzMy04MzZjLTg0MDIyNTE3MTgzNiJ9.eyJ2ZXIiOjksImF1aWQiOiI4M2Y5ZWU0YjEwZWIwMWM3OWI1OGZhZTAzNmU4OGY3ZCIsImNvZGUiOiJSNnNCU2dmNkFmUXo2YnA1UjZtVHhDUHJ2VXI0Nk5tV2ciLCJpc3MiOiJ6bTpjaWQ6czQzUkJob3hSamF3TEJCN2RzYnV1USIsImdubyI6MCwidHlwZSI6MCwidGlkIjowLCJhdWQiOiJodHRwczovL29hdXRoLnpvb20udXMiLCJ1aWQiOiJFNXh4cVgxd1NwcWdLUkhZc2lxLUtBIiwibmJmIjoxNjkzOTE0MDUyLCJleHAiOjE2OTM5MTc2NTIsImlhdCI6MTY5MzkxNDA1MiwiYWlkIjoiVWlKUWd4enFTaGE3TzVwTnhTWUU2USJ9.mUrzjAvSm1LQJHlWvUDkNdE3vtACfUM6p0ybcHaLgyrsnfsP904EG83ZCuAzvhsYgEYMdkmEnBrtAow9Xhv-TA",
                            "Content-Type: application/json"
                        ),
                    ));

                    $response = curl_exec($curl);
                    curl_close($curl);
                    $responseJSON = json_decode("[" . $response . "]", true);
                    $join_url = $responseJSON[0]["join_url"];
                    global $wpdb;
                    $data = array(
                        'course_name' => $course_name,
                        'price' => $price,
                        'date' => $date,
                        'repeat_every' => $repeat,
                        'description' => $description,
                        'url' => $join_url,
                        'product_id' => $product_id,
                        'max_registrations' => $maxReg,
                        'event_id' => $eventID
                    );
                    $wpdb->insert($table_name, $data);
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

            if (isset($_REQUEST['testmeeting']) || isset($_REQUEST['code'])) {

                // Check if the code has already been executed
                session_start(); // Start a session (if not already started)
                $access_token = "temp";

                if (!isset($_REQUEST['code'])) {
                    header("Location: https://zoom.us/oauth/authorize?response_type=code&client_id=s43RBhoxRjawLBB7dsbuuQ&redirect_uri=https://jolly-hamilton.185-101-158-220.plesk.page/wp-admin/admin.php?page=courses_plugin");
                }
                if (isset($_REQUEST['code'])) {
                    $client_id = 's43RBhoxRjawLBB7dsbuuQ';
                    $client_secret = 'JQ2nDt8lFZtynkT4R34sXUBLCXGO6btV';
                    $code = $_GET['code'];
                    $redirect_uri = 'jolly-hamilton.185-101-158-220.plesk.page/wp-admin/admin.php?page=courses_plugin';

        $clientIDandSecret = base64_encode($client_id . ':' . $client_secret);
        // Create the Authorization header
        $authorizationHeader = array("Host: zoom.us", "Authorization: Basic " . $clientIDandSecret, "Content-Type: application/x-www-form-urlencoded");
        $tempheader = base64_encode($client_id . ':' . $client_secret);
                    function base64URLEncode($str)
                    {
                        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($str));
                    }

                    function sha256_ASCII($buffer)
                    {
                        return hash('sha256', $buffer, true);
                    }

        $codeVerifier = base64URLEncode($clientIDandSecret);
                    $codeChallenge = base64URLEncode(sha256_ASCII($codeVerifier));
        $temp = base64_decode($tempheader, true);
        echo "\nbase 64\n";
        var_dump($temp);
        echo "\nbase 64 url\n";

                    // Prepare the data for the POST request
                    $data = array(
                        "grant_type" => "authorization_code",
                        "code" => $code,
                        "redirect_uri" => $redirect_uri,
            "account_id" => 5066160394
                     //   "code_verifier" => $codeVerifier,
                      //  "code_challange" => $codeChallenge
                    );
        var_dump($data);
        // Initialize cURL session
        $curl = curl_init("https://zoom.us/oauth/token&code=$code");

                    // Set cURL options
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $authorizationHeader);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

                    // Execute cURL request
                    $response = curl_exec($curl);

                    // Close cURL session
                    curl_close($curl);

                    // Now, $response contains the response from the server, including the access token
                    var_dump($response);
                }
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
<form method="post">
    <input type="submit" name="testmeeting" value="TestMeeting" /><br>
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
