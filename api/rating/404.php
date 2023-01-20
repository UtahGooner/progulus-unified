<?php

use progulusAPI\SongRating;

require_once 'SongRating.php';
require_once 'json_utils.php';

$apiPath = substr(__DIR__, strlen(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT'))) . '/';
$response = [];
try {
    $requestURI = filter_input(INPUT_SERVER, 'REQUEST_URI');
    $parsedURL = parse_url($requestURI);
    $searchParams = preg_split('/\//', str_replace($apiPath, '', $parsedURL['path']));

    $songID = (int) urldecode($searchParams[0] ?? 0);

    $rating = new SongRating($songID);
    $rating->load();
    $response['rating'] = $rating;
} catch (\Exception $ex) {
    $response['error'] = $ex->getMessage();
}

json_send($response, JSON_PRETTY_PRINT);

