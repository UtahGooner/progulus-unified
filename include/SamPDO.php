<?php
namespace progulusAPI;
use \Exception;
use \PDO;
use \PDOException;

require_once 'autoload.inc.php';

class SamPDO
{
    /**
     *
     * @var PDO
     */
    private static $instance;

    /**
     *
     * @var PDOException
     */
    public static $exception;

    /**
     * @return PDO
     * @throws Exception;
     */
    public static function singleton(): PDO
    {
        $display_errors = ini_get('display_errors');
        ini_set('display_errors', false);

        if (!isset(self::$instance)) {
            try {
                self::$instance = new PDO(
                    SAM_DSN,
                    SAM_USERNAME,
                    SAM_PASSWORD,
                    [
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                    ]
                );
//                self::$instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                self::$instance->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
                self::$instance->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY , true);
            } catch (PDOException $e) {
                self::$exception = $e;
//                trigger_error("Connection failed: " . $e->getMessage(), E_USER_NOTICE);
                throw new \Exception('Unable to connect to database');
            }
        }

        ini_set('display_errors', $display_errors);
        return self::$instance;
    }

    public function __clone()
    {
        trigger_error("Clone is not allowed", E_USER_ERROR);
    }
}
