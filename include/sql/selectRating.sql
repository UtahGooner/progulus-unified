SELECT s.id             AS songID,
       s.rating,
       s.votes,
       IFNULL((
                  SELECT rating
                  FROM samdb.song_rating
                  WHERE songID = s.id
                    AND userID = :userID
                  ), 0) AS userRating
FROM samdb.songlist s
WHERE s.id in (:songID)
