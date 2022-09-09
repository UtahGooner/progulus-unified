UPDATE songlist s
    INNER JOIN (
               SELECT songID,
                      SUM(rating)                 AS totalRating,
                      COUNT(rating)               AS totalVotes,
                      SUM(rating) / COUNT(rating) AS avgRating
               FROM song_rating
               GROUP BY songID) r ON r.songID = s.id
SET s.rating = r.avgRating,
    s.votes  = r.totalVotes
WHERE ABS(s.rating - r.avgRating) > 0.01
   OR r.totalVotes <> s.votes
