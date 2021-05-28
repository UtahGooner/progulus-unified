<?php
require_once 'autoload.inc.php';

class ForumsPDO {
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
     *
     * @return PDO|false
     */
    public static function singleton()
    {
        if (!isset(self::$instance)) {
            try {
                self::$instance = new PDO(
                    FORUM_DSN,
                    FORUM_USERNAME,
                    FORUM_PASSWORD,
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
                );
                return self::$instance;
            } catch (PDOException $e) {
                self::$exception = $e;
                trigger_error("Connection failed: " . $e->getMessage(), E_USER_NOTICE);
                return false;
            }
        }
        return self::$instance;
    }

    public function __clone()
    {
        trigger_error("Clone is not allowed", E_USER_ERROR);
    }
}

?>
