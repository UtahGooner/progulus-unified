<?php

//namespace ProgulusAPI;

require_once 'SamPDO.php';
require_once 'autoload.inc.php';
require_once 'json_utils.php';
require_once 'Search.php';

use progulusAPI\Search;

$response = [];

try {
    $requestURI = filter_input(INPUT_SERVER, 'REQUEST_URI');
    $parsedURL = parse_url($requestURI);

    $searchParams = preg_split('/\//', str_replace('/api/search/', '', $parsedURL['path']));

    $artist = urldecode($searchParams[0] ?? '');
    $album = urldecode($searchParams[1] ?? '');

    $response['params'] = $searchParams;
    $response['search'] = [];

    $searchResults = [];
    if ($artist) {
        $search = new Search($artist);
        $response['artist'] = $search->getResults();
        $response['search'][] = $search;
        if (!count($response['artist'])) {
            header('HTTP/1.1 404 Not Found');
            exit;
        }
        if (!$album) {
            $search->for = Search::SEARCH_FOR_ALBUM;
            $response['albums'] = $search->getResults();
            $response['search'][] = $search;
        }
    }
    if ($album) {
        $search = new Search($artist, $album);
        $response['album'] = $search->getResults();
        $response['search'][] = $search;

        $search->for = Search::SEARCH_FOR_SONG;
        $response['tracks'] = $search->getResults();
        $response['search'][] = $search;

    }
    $search = new Search($artist, $album);
} catch (\Exception $ex) {
    $response['error'] = $ex->getMessage();
}

json_send($response, JSON_PRETTY_PRINT);

