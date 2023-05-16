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

// Read the contents of the secret file
$secretsFile = getenv('HOME') . '/Inc/Base/secrets/zoom.json';
$secrets = json_decode(file_get_contents($secretsFile), true);

// Use the API key and secret in your Zoom API requests
$apiKey = $secrets['api_key'];
$apiSecret = $secrets['api_secret'];

// Make an API request using the API key and secret
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.zoom.us/v2/users/me");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Basic " . base64_encode("{$apiKey}:{$apiSecret}")
));
$response = curl_exec($ch);
curl_close($ch);

echo $response;
