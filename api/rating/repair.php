<?php

use progulusAPI\SongRating;

require_once 'autoload.inc.php';
require_once 'json_utils.php';
require_once 'include/SongRating.php';

$response = [];

global $user;
$userID = (int) $user->data['user_id'];
$response['user'] = $userID;
if ($userID !== 2 && $userID !== 24) {
    $response['error'] = 'Admin required';
    json_send($response, JSON_PRETTY_PRINT);
}
try {
    $response['updates'] = SongRating::repair();
} catch (\Exception $ex) {
    $response['error'] = $ex->getMessage();
}

json_send($response, JSON_PRETTY_PRINT);
