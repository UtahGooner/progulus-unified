<?php

namespace progulusAPI;

use Exception;
use PDO;

require_once 'SamPDO.php';
require_once 'autoload.inc.php';

class Rating {
    public $rating = 0;
    public $votes = 0;

    public function __construct($rating, $votes)
    {
        $this->rating = (float) $rating;
        $this->votes = (int) $votes;
    }

    /**
     * @param array $row
     * @param mixed $row['rating']
     * @param mixed $row['votes']
     * @return Rating
     */
    public static function fromSQLRow($row): Rating
    {
        return new Rating($row['rating'], $row['votes']);
    }
}

class SongRating {
    private $userName = '';
    private $userID = 0;

    public $songID = 0;
    public $rating = 0;
    public $votes = 0;
    public $userRating = 0;
    public $ratings = [];

    public function __construct($songID)
    {
        global $user;
        $this->userID = (int) $user->data['user_id'] ?? 0;
        $this->userName = $user->data['username'] ?? '';
        $this->songID = (int) $songID;
    }

    /**
     * @param float $rating
     * @return $this
     * @throws Exception
     */
    public function rate(float $rating): SongRating
    {
        if ($this->userID <= 1) {
            throw new Exception('You must be logged in to rate songs', 401);
        }
        if ($rating < 0 || $rating > 5) {
            throw new Exception("Invalid Rating value: please choose a number between 0 and 5", 400);
        }
        try {
            $pdo = SamPDO::singleton();
            $pdo->beginTransaction();
            if ($rating === 0.0) {
                $query = file_get_contents(__DIR__ . '/sql/deleteRating.sql');
                $ps = $pdo->prepare($query);
                $ps->bindValue(':songID', $this->songID, PDO::PARAM_INT);
                $ps->bindValue(':userID', $this->userID, PDO::PARAM_INT);
            } else {
                $query = file_get_contents('./sql/setRating.sql');
                $ps = $pdo->prepare($query);
                $ps->bindValue(':songID', $this->songID, PDO::PARAM_INT);
                $ps->bindValue(':userID', $this->userID, PDO::PARAM_INT);
                $ps->bindValue(':rating', $rating);
                $ps->bindValue(':username', $this->userName);
            }
            $ps->execute();
            $ps->closeCursor();

            $qUpdate = file_get_contents(__DIR__ . './sql/updateSongRating.sql');
            $psUpdate = $pdo->prepare($qUpdate);
            $psUpdate->bindValue(':songID', $this->songID, PDO::PARAM_INT);
            $psUpdate->execute();
            $psUpdate->closeCursor();

            $pdo->commit();
            return $this;
        } catch (\Exception $ex) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            trigger_error($ex->getMessage());
            throw new Exception($ex->getMessage());
        }

    }

    /**
     * @return $this
     * @throws Exception
     */
    public function load(): SongRating
    {
        try {
            $pdo = SamPDO::singleton();
            $qRating = file_get_contents(__DIR__ . '/sql/selectRating.sql');
            $psRating = $pdo->prepare($qRating);
            $psRating->bindValue(':userID', $this->userID, PDO::PARAM_INT);
            $psRating->bindValue(':songID', $this->songID, PDO::PARAM_INT);
            $psRating->execute();
            while ($row = $psRating->fetch(PDO::FETCH_ASSOC)) {
                $this->rating = (float) $row['rating'];
                $this->votes = (int) $row['votes'];
                $this->userRating = (float) $row['userRating'];
            }
            $psRating->closeCursor();

            $qRatings = file_get_contents('./selectRatings.sql');
            $psRatings = $pdo->prepare($qRatings);
            $psRatings->bindValue(':songID', $this->songID, PDO::PARAM_INT);
            $psRatings->execute();
            $this->ratings = [];
            while ($row = $psRatings->fetch(PDO::FETCH_ASSOC)) {
                $this->ratings[] = new Rating($row['rating'], $row['votes']);
            }
            $psRatings->closeCursor();
            return $this;
        } catch (\Exception $ex) {
            trigger_error($ex->getMessage());
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * @return SongRating[]
     * @throws Exception
     */
    public static function loadList($songIDs = []): array
    {
        if (count($songIDs) === 0) {
            return [];
        }
        global $user;
        $userID = $user->data['user_id'] ?? 0;

        try {
            $qRating = file_get_contents(__DIR__ . '/sql/selectRating.sql');
            $qRatings = file_get_contents(__DIR__ . '/sql/selectRatings.sql');

            /**
             * @var SongRating[] $ratings
             */
            $ratings = [];
            $songID = 0;
            $pdo = SamPDO::singleton();
            $psRating = $pdo->prepare($qRating);
            $psRating->bindValue(':userID', $userID, PDO::PARAM_INT);
            $psRating->bindParam(':songID', $songID, PDO::PARAM_INT);

            $psRatings = $pdo->prepare($qRatings);
            $psRatings->bindParam(':songID', $songID, PDO::PARAM_INT);

            foreach($songIDs as $songID) {
                $psRating->execute();
                $rating = new SongRating($songID);
                while ($row = $psRating->fetch(PDO::FETCH_ASSOC)) {
                    $rating->votes = (int) $row['votes'];
                    $rating->rating = (float) $row['rating'];
                    $rating->userRating = (float) $row['userRating'];
                }
                $psRatings->execute();
                while ($row = $psRatings->fetch(PDO::FETCH_ASSOC)) {
                    $rating->ratings[] = Rating::fromSQLRow($row);
                }
                $ratings[$rating->songID] = $rating;
            }
            $psRating->closeCursor();
            $psRatings->closeCursor();

            return $ratings;
        } catch (\Exception $ex) {
            trigger_error($ex->getMessage());
        }
        throw new Exception($ex->getMessage());
    }

    /**
     * @return int
     * @throws Exception
     */
    public static function repair(): int
    {
        try {
            $pdo = SamPDO::singleton();
            $query = file_get_contents(__DIR__ . '/sql/repairRatings.sql');
            $ps = $pdo->prepare($query);
            $ps->execute();
            return $ps->rowCount();
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
