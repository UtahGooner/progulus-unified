<?php

namespace ProgulusAPI;

use \Exception;

require_once 'SamPDO.php';
require_once 'autoload.inc.php';
require_once 'json_utils.php';
require_once 'phpbb3.userdata.php';
require_once 'Search.php';

$response = [];
try {
    $search = new Search();
    $search->parseQueryString();
    $response['search'] = $search;
    $response[$search->responseType] = $search->getResults();
} catch (\Exception $ex) {
    $response['error'] = $ex->getMessage();
}

json_send($response, JSON_PRETTY_PRINT);
