<?php

namespace progulusAPI;


use \Exception;
use \PDO;

require_once 'SamPDO.php';
require_once 'autoload.inc.php';
require_once 'json_utils.php';


global $user;
$userID = $user->data['user_id'];
$userIPAddress = $user->ip;
$userName = $user->data['username'];

$response = [];
if ($userID === '24') {
//    $response['user'] = $user;
}
$response['user_id'] = $userID;
//$response['user_ip'] = $userIPAddress;
//$response['user_name'] = $userName;
$response['status'] = 'pending';
$songID = filter_input(INPUT_POST, 'songid', FILTER_VALIDATE_INT);
if (empty($songID)) {
    $songID = filter_input(INPUT_GET, 'songid', FILTER_VALIDATE_INT);
}

if (empty($songID)) {
    $requestURI = filter_input(INPUT_SERVER, 'REQUEST_URI');
    $parsedURL = parse_url($requestURI);

    $searchParams = preg_split('/\//', str_replace('/api/request/', '', $parsedURL['path']));

    $songID = urldecode($searchParams[0] ?? '');
}

if (empty($songID)) {
    $response['status'] = 'Invalid song ID';
    json_send($response, JSON_PRETTY_PRINT);
    exit;
}

if ($userID < '2') {
    $response['status'] = 'Login is required';
    json_send($response, JSON_PRETTY_PRINT);
    exit;
}

$result = 0;
$request_id = 0;
$pdo = SamPDO::singleton();
$ps = $pdo->prepare("CALL `samdb`.`test_request`(:song_id, :user_id, :user_ip, :user_name, null, @result, @request_id)");
$ps->bindValue(':song_id', $songID, PDO::PARAM_INT);
$ps->bindValue(':user_id', $userID, PDO::PARAM_INT);
$ps->bindValue(':user_ip', $userIPAddress, PDO::PARAM_STR);
$ps->bindValue(':user_name', $userName, PDO::PARAM_STR);
if ($ps->execute() === false) {
    $response['error'] = $ps->errorInfo();
    json_send($response, JSON_PRETTY_PRINT);
    exit;
}

$ps = $pdo->prepare("SELECT @result as result, @request_id as request_id");
if ($ps->execute() === false) {
    $response['error'] = $ps->errorInfo();
    json_send($response, JSON_PRETTY_PRINT);
    exit;
}

while ($row = $ps->fetch(PDO::FETCH_ASSOC)) {
    $result = (int) $row['result'];
    $request_id = (int) $row['request_id'];
}


/*
	const ELEMENT_RESULT = "Result";
	const ELEMENT_TEXT = "Text";
	const ELEMENT_SONG_ID = "SongID";
	const ELEMENT_REQUEST_ID = "RequestID";
	const ELEMENT_SIGNATURE = "Signature";

	const RESULT_INITIALIZED = -1;
	const RESULT_UNKNOWN = 0;
	const RESULT_INVALID_SONG = 1;
	const RESULT_NO_LOGIN = 2;
	const RESULT_TOO_LONG = 3;
	const RESULT_ARTIST_LIMIT_EXCEEDED = 4;
	const RESULT_USER_SONG_LIMIT_EXCEEDED = 5;
	const RESULT_USER_ARTIST_LIMIT_EXCEEDED = 6;
	const RESULT_ARTIST_IN_QUEUE = 7;
	const RESULT_TOO_LONG_FOR_QUEUE = 8;
	const RESULT_ARTIST_RECENTLY_PLAYED = 9;
	const RESULT_ALBUM_RECENTLY_PLAYED = 10;
	const RESULT_SONG_RECENTLY_PLAYED = 11;
	const RESULT_SAMDB_DOWN = 12;
	const RESULT_USER_EPIC_QUEUED = 13;
	const RESULT_OK = 200;
	const RESULT_HOST_NOT_DEFINED = 800;
	const RESULT_HOST_PORT_NOT_DEFINED = 801;
	const RESULT_SAM_DATA_EMPTY = 803;
	const RESULT_SAM_DATA_NOT_VALID = 804;
	const RESULT_INVALID_REQUESTID = 805;
*/


$errors = [
    0 => 'Something has gone wrong, Dave. Daisy, Daisy, give me your answer do',
    2 => 'You must be logged in to request songs. Please log in first.',
    3 => 'Epics are not allowed right now',
    4 => 'Artist limit has been exceeded, try a different artist',
    5 => 'You have queued a bunch already, try again when the queue is shorter',
    6 => 'Artist limit has been exceeded, try exploring a different artist',
    7 => 'Artist is already queued',
    9 => 'This artist has been played in the last three hours',
    10 => 'This album has been played in the last six hours',
    11 => 'This song has been played recently',
    13 => 'You already have an epic queued',
    404 => 'Song not found',
];

$response['result'] = $result;
if ($result !== 200) {
    if (!isset($errors[$result])) {
        $response['status'] = 'error';
        $response['error'] = $errors[0];
    } else {
        $response['status'] = 'failed';
        $response['error'] = $errors[$result];
    }
    $response['success'] = false;
} else {
    $response['status'] = 'queued';
    $response['success'] = true;
}
$response['request_id'] = $request_id;
$response['song_id'] = $songID;

json_send($response, JSON_PRETTY_PRINT);
