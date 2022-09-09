SELECT songID,
       rating,
       COUNT(*) AS votes
FROM samdb.song_rating
WHERE songID IN (:songID)
GROUP BY songID, rating
