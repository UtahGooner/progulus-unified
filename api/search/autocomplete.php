<?php

namespace ProgulusAPI;

use \Exception;

require_once 'SamPDO.php';
require_once 'autoload.inc.php';
require_once 'SearchDefaults.php';
require_once 'json_utils.php';

$defaults = SearchDefaults::load(true);

$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
$get = filter_input(INPUT_GET, 'get', FILTER_SANITIZE_STRING);
$build = filter_input(INPUT_GET, 'build', FILTER_SANITIZE_STRING);

if ($search) {
    $artists = array_filter($defaults->artists, function ($artist) use ($search) {
        return preg_match("/{$search}/i", $artist);
    });
    json_send(array_values($artists), JSON_PRETTY_PRINT, 3600);
    exit;
}

if ($build) {
    $defaults = SearchDefaults::writeSearch(true);
}

$response = [];
$response['genres'] = $defaults->genres;
$response['years'] = $defaults->years;
$response['countries'] = $defaults->countries;

json_send($response, JSON_PRETTY_PRINT, 3600);

