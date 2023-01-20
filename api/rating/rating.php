<?php

namespace progulusAPI;
use \Exception;
use \PDO;

require_once 'SamPDO.php';
require_once 'autoload.inc.php';
require_once 'json_utils.php';


global $user;
$userID = $user->data['user_id'];
$userName = $user->data['username'];

$songID = (int)filter_input(INPUT_GET, 'songID', FILTER_VALIDATE_INT);

$response = [];
$response['user'] = (int) $userID;
$response['songID'] = (int) $songID;

try {
    $pdo = SamPDO::singleton();
    $qRating = file_get_contents('./selectRating.sql');
    $psRating = $pdo->prepare($qRating);
    $psRating->bindValue(':userID', $userID, PDO::PARAM_INT);
    $psRating->bindValue(':songID', $songID, PDO::PARAM_INT);
    $psRating->execute();
    while ($row = $psRating->fetch(PDO::FETCH_ASSOC)) {
        $row['rating'] = (float) $row['rating'];
        $row['votes'] = (int) $row['votes'];
        $row['userRating'] = (float) $row['userRating'];
        $response['rating'] = $row;
    }
    $psRating->closeCursor();

    $qRatings = file_get_contents('./selectRatings.sql');
    $psRatings = $pdo->prepare($qRatings);
    $psRatings->bindValue(':songID', $songID, PDO::PARAM_INT);
    $psRatings->execute();
    $response['ratings'] = [];
    while ($row = $psRatings->fetch(PDO::FETCH_ASSOC)) {
        $row['rating'] = (float) $row['rating'];
        $row['votes'] = (int) $row['votes'];
        $response['ratings'][] = $row;
    }
    $psRatings->closeCursor();


} catch (\Exception $ex) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    $response['error'] = $ex->getMessage();
}

json_send($response, JSON_PRETTY_PRINT);
