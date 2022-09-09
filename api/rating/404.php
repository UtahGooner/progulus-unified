<?php

use progulusAPI\SongRating;

require_once 'SongRating.php';
require_once 'json_utils.php';

$apiPath = substr(__DIR__, strlen(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT'))) . '/';
$requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING);

$response = [];
try {
    $requestURI = filter_input(INPUT_SERVER, 'REQUEST_URI');
    $parsedURL = parse_url($requestURI);
    $searchParams = preg_split('/\//', str_replace($apiPath, '', $parsedURL['path']));

    $songID = (int) urldecode($searchParams[0] ?? 0);
    $rating = new SongRating($songID);

    switch ($requestMethod) {
        case 'POST':
        case 'PUT':
            $userRating = json_post();
            $response['todo:save rating'] = $userRating;
//            $rating->rate($userRating);
            break;
        default:
            $rating->load();
            $response['rating'] = $rating;
            break;
    }

} catch (\Exception $ex) {
    $response['error'] = $ex->getMessage();
    $response['error_code'] = $ex->getCode();
}

json_send($response, JSON_PRETTY_PRINT);

