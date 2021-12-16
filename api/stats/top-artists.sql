SELECT s.artist,
       SUM(r.all_time)         AS all_time,
       SUM(r.last_year)        AS last_year,
       SUM(r.last_month)       AS last_month,
       SUM(r.last_week)        AS last_week,
       AVG(song_rating.rating) AS user_rating,
       count(distinct album) as albums,
       count(*) as songs,
       sum(s.votes) as votes,
       sum(ifnull(s.rating,0) * ifnull(s.votes, 0)) / sum(ifnull(s.votes, 0)) as rating
FROM songlist s
     INNER JOIN (
                SELECT songID,
                       COUNT(*)                                           AS all_time,
                       SUM(DATE_ADD(timestamp, INTERVAL 1 YEAR) > NOW())  AS last_year,
                       SUM(DATE_ADD(timestamp, INTERVAL 1 MONTH) > NOW()) AS last_month,
                       SUM(DATE_ADD(timestamp, INTERVAL 1 WEEK) > NOW())  AS last_week
                FROM samdb.request_history
                WHERE IFNULL(:requestUserId, 0) = 0
                   OR userid = :requestUserId
                GROUP BY songID
                ) r
                ON s.id = r.songID
     LEFT JOIN  song_rating
                ON song_rating.songID = s.id AND song_rating.userID = :userID
WHERE r.all_time > 0
GROUP BY s.artist
ORDER BY all_time DESC
LIMIT :limit
