SELECT s.rating,
       s.votes,
       (
       SELECT rating
       FROM samdb.song_rating
       WHERE songID = s.id
         AND userID = :userID) AS userRating
FROM samdb.songlist s
WHERE s.id = :songID
