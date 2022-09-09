<?php

namespace progulusAPI;


use \Exception;
use \PDO;

require_once 'SamPDO.php';
require_once 'autoload.inc.php';
require_once 'json_utils.php';
require_once 'CurrentSong.php';
require_once 'SongRating.php';


global $user;
$userID = $user->data['user_id'];

$response = [];
$response['user'] = $userID;
$response['songs'] = [];
$response['queue'] = [];

try {
    $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);
    $offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT);
    $before = filter_input(INPUT_GET, 'before', FILTER_VALIDATE_INT);

    $response['songs'] = CurrentSong::loadCurrentSongs($userID, $limit ?? 10, $offset ?? 0, $before ?? 0);
    $response['queue'] = CurrentSong::loadQueue($userID);

    $songIDs = [];
    foreach($response['songs'] as $song) {
        $songIDs[] = $song->id;
    }
    foreach($response['queue'] as $song) {
        $songIDs[] = $song->id;
    }
    $ratings = SongRating::loadList($songIDs);
    foreach($response['songs'] as $song) {
        $song->ratings = $ratings[$song->id];
    }
    foreach($response['queue'] as $song) {
        $song->ratings = $ratings[$song->id];
    }

} catch (\Exception $ex) {
    $response['error'] = $ex->getMessage();
}

json_send($response, JSON_PRETTY_PRINT);
