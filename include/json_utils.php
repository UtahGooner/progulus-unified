<?php

if (!function_exists('json_post')) {
    /**
     * Parses a JSON post with filter_input_array, falls back to regular POST if not Content-Type: application/json
     * @param array $filter PHP_FILTER types
     * @param bool $assoc Convert to associative array.
     * @return mixed|null
     */
    function json_post(array $filter = [], bool $assoc = false)
    {
        try {
            $post = file_get_contents("php://input");
            $parsed = json_decode($post, $assoc);
            if (count($filter)) {
                return filter_var_array(get_object_vars($parsed), $filter);
            }
            return $parsed;
        } catch (\Exception $ex) {
            trigger_error($ex->getMessage(), E_USER_NOTICE);
            return null;
        }
    }
}

if (!function_exists('json_send')) {

    /**
     * @param mixed $value
     * @param int $flags
     */
    function json_send($value, int $flags = 0)
    {
        if (headers_sent($filename, $line)) {
            trigger_error("Headers have already been sent in file '{$filename}' at line {$line}",
                E_USER_ERROR);
        }
        ob_start();
        try {
            echo json_encode($value, $flags);
        } catch (\Exception $ex) {
            echo json_encode(['parse_error' => $ex->getMessage()]);
        }
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header("Content-type: application/json");
        header("Content-Length: " . ob_get_length());
        ob_end_flush();
    }
}
