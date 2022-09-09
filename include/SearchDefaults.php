<?php

namespace progulusAPI;

use Exception;
use PDO;


class SearchDefaults {
    private static $pdo;
    private static $filename = '/serialize.search.php';

    public $years = [];
    public $countries = [];
    public $artists = [];
    public $genres = [];


    public function __construct()
    {
        try {
            self::$pdo = SamPDO::singleton();
        } catch (\Exception $ex) {
            trigger_error($ex->getMessage());
        }
    }

    public static function load($isAdmin = false) {
        if (!file_exists(__DIR__ . self::$filename)) {
            return SearchDefaults::writeSearch();
        }
        if ($isAdmin && time() - filemtime(__DIR__  . self::$filename) > 86400) {
            return SearchDefaults::writeSearch();
        }

        $content = file_get_contents(__DIR__ . self::$filename);
        return unserialize($content);
    }

    public static function writeSearch(): ?SearchDefaults
    {
        try {
            trigger_error('writeSearch()');
            $search = new SearchDefaults();
            $search->buildDefaults();
            $status = file_put_contents(__DIR__ . self::$filename, serialize($search));
            trigger_error(json_encode($status));
            return $search;
        } catch (\Exception $ex) {
            trigger_error($ex->getMessage());
        }
        return null;
    }

    /**
     * @throws Exception
     */
    public function buildDefaults(): bool
    {
        if (!self::$pdo) {
            throw new Exception(__FILE__ . ': Unable to connect to database');
        }
        try {
            $this->buildYears();
            $this->buildCountries();
            $this->buildArtists();
            $this->buildGenres();
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function buildYears() {
        $this->years = [];
        $query = "SELECT distinct albumyear from samdb.songlist order by albumyear DESC";
        $ps = self::$pdo->prepare($query);
        $ps->execute();
        while ($row = $ps->fetch(PDO::FETCH_ASSOC)) {
            $this->years[] = $row['albumyear'];
        }
        $ps->closeCursor();
        $count = count($this->years);
        trigger_error("years: {$count}");
    }

    public function buildCountries() {
        $this->countries = [];
        $query = "SELECT DISTINCT label as country FROM samdb.songlist order by country ";
        $ps = self::$pdo->prepare($query);
        $ps->execute();
        while($row = $ps->fetch(PDO::FETCH_ASSOC)) {
            $this->countries[] = $row['country'];
        }
        $ps->closeCursor();
        $count = count($this->countries);
        trigger_error("countries: {$count}");

    }

    public function buildArtists() {
        $this->artists = [];
        $query = "SELECT DISTINCT artist FROM samdb.songlist WHERE songtype = 'S' order by artist";
        $ps = self::$pdo->prepare($query);
        $ps->execute();
        while($row = $ps->fetch(PDO::FETCH_ASSOC)) {
            $this->artists[] = $row['artist'];
        }
        $ps->closeCursor();
        $count = count($this->artists);
        trigger_error("artists: {$count}");

    }

    public function buildGenres() {
        $genres = [];
        $query = "SELECT DISTINCT genre FROM samdb.songlist WHERE songtype = 'S' order by genre";
        $ps = self::$pdo->prepare($query);
        $ps->execute();
        while($row = $ps->fetch(PDO::FETCH_ASSOC)) {
            $t_genres = explode(",", $row['genre']);
            foreach ($t_genres as $genre) {
                $genres[] = trim($genre);
            }
        }
        $ps->closeCursor();
        $genres = array_unique($genres);
        sort($genres);
        $this->genres = $genres;
        $count = count($this->genres);
        trigger_error("genres: {$count}");

    }

}
