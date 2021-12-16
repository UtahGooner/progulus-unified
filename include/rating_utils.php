<?php
namespace progulus;

/**
 * @param string $rating
 * @return float[]
 */
function  splitSearchRating(string $rating = '') {
    $rating = explode(',', $rating);
    $response = [0,5];
    if (count($rating) === 1 && $rating[0] === '') {
        return $response;
    }
    switch (count($rating)) {
        case 1:
            $response[0] = ((float) $rating[0]) - 0.25;
            $response[1] = ((float) $rating[0]) + 0.25;
            break;
        case 2:
            $response[0] = ((float) $rating[0]);
            $response[1] = ((float) $rating[1]);
            break;
    }
    if ($response[0] > 5 || $response[0] < 0) {
        $response[0] = 0;
    }
    if ($response[1] > 5 || $response[1] < 0) {
        $response[1] = 5;
    }
    return $response;
}

