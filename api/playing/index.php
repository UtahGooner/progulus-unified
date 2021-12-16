<?php

namespace progulusAPI;


use \Exception;
use \PDO;

require_once 'SamPDO.php';
require_once 'autoload.inc.php';
require_once 'json_utils.php';
require_once 'CurrentSong.php';

global $user;
$userID = $user->data['user_id'];

$response = [];
$response['user'] = $userID;
$response['songs'] = [];
$response['queue'] = [];
try {
    $querySongs = file_get_contents('./current.sql');
    $queryQueue = file_get_contents('./queue.sql');

    $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);
    $offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT);
    $before = filter_input(INPUT_GET, 'before', FILTER_VALIDATE_INT);

    $pdo = SamPDO::singleton();
    if (!$pdo) {
        throw new Exception('Unable to connect to database');
    }
    $ps = $pdo->prepare($querySongs);
    $ps->bindValue(':offset', $offset ?? 0, PDO::PARAM_INT);
    $ps->bindValue(':limit', $limit ?? 10, PDO::PARAM_INT);
    $ps->bindValue(':userID', $userID, PDO::PARAM_INT);
    $ps->bindValue(':before', $before ?? 0, PDO::PARAM_INT);

    if (!$ps->execute()) {
        throw new Exception($ps->errorInfo()[2]);
    }

    while ($row = $ps->fetch(PDO::FETCH_ASSOC)) {
        $response['songs'][] = new CurrentSong($row);
    }

    $ps = $pdo->prepare($queryQueue);
    $ps->bindValue('userID', $userID, PDO::PARAM_INT);
    if (!$ps->execute()) {
        throw new Exception($ps->errorInfo()[2]);
    }

    while ($row = $ps->fetch(PDO::FETCH_ASSOC)) {
        $response['queue'][] = new CurrentSong($row);
    }
} catch (\Exception $ex) {
    $response['error'] = $ex->getMessage();
}

json_send($response, JSON_PRETTY_PRINT);
