SELECT rating, COUNT(*) AS votes
FROM samdb.song_rating
WHERE songID = :songID
GROUP by rating
