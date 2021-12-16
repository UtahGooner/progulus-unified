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

$querySongs = file_get_contents('./current.sql');

$limit = filter_input(INPUT_GET, 'limit', FILTER_SANITIZE_NUMBER_INT);
if (empty($limit) || $limit > 50) {
    $limit = 10;
}
$offset = filter_input(INPUT_GET, 'offset', FILTER_SANITIZE_NUMBER_INT);
if (empty($offset)) {
    $offset = 0;
}

$before = filter_input(INPUT_GET, 'before', FILTER_SANITIZE_NUMBER_INT);
if (empty($before)) {
    $before = 0;
}

$response = [];
$response['songs'] = [];
try {
    $pdo = SamPDO::singleton();
    if (!$pdo) {
        throw new Exception('Unable to connect to database');
    }
    $ps = $pdo->prepare($querySongs);
    $ps->bindValue(':offset', $offset, PDO::PARAM_INT);
    $ps->bindValue(':limit', $limit, PDO::PARAM_INT);
    $ps->bindValue(':userID', $userID, PDO::PARAM_INT);
    $ps->bindValue(':before', $before, PDO::PARAM_INT);

    if (!$ps->execute()) {
        throw new Exception($ps->errorInfo()[2]);
    }

    while ($row = $ps->fetch(PDO::FETCH_ASSOC)) {
        $response['songs'][] = new CurrentSong($row);
    }
} catch (\Exception $ex) {
    $response['error'] = $ex->getMessage();
}
json_send($response, JSON_PRETTY_PRINT);
