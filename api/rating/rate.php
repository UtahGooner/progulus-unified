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

$post = json_post(['songID' => FILTER_VALIDATE_INT, 'rating' => FILTER_VALIDATE_FLOAT]);

$songID = (int) $post['songID'];
$rating = (float) $post['rating'];

$response = [];
$response['user'] = $userID;
$response['songID'] = $songID;

if ($userID <= 1) {
    $response['error'] = "You must be logged in to rate songs.";
    json_send($response, JSON_PRETTY_PRINT);
    exit;
}

if (!$songID) {
    $response['error'] = "Invalid Song ID";
    json_send($response, JSON_PRETTY_PRINT);
    exit;
}

if ($rating < 0 || $rating > 5) {
    $response['error'] = "Invalid Rating value: please choose a number between 0 and 5";
    json_send($response, JSON_PRETTY_PRINT);
    exit;
}

try {
    $pdo = SamPDO::singleton();
    $pdo->beginTransaction();
    if ($rating === 0.0) {
        $query = file_get_contents('./deleteRating.sql');
        $ps = $pdo->prepare($query);
        $ps->bindValue(':songID', $songID);
        $ps->bindValue(':userID', $userID, PDO::PARAM_INT);
        $ps->execute();
        $ps->closeCursor();
    } else {
        $query = file_get_contents('./setRating.sql');
        $ps = $pdo->prepare($query);
        $ps->bindValue(':songID', $songID);
        $ps->bindValue(':userID', $userID, PDO::PARAM_INT);
        $ps->bindValue(':rating', $rating);
        $ps->bindValue(':username', $userName);
        $ps->execute();
        $ps->closeCursor();
    }
    $qUpdate = file_get_contents('./updateSongRating.sql');
    $psUpdate = $pdo->prepare($qUpdate);
    $psUpdate->bindValue(':songID', $songID, PDO::PARAM_INT);
    $psUpdate->execute();
    $psUpdate->closeCursor();
    $pdo->commit();

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
