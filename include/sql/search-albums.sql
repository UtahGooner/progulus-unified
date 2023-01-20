SELECT s.artist,
       s.album,
       SUM(s.duration)                                                                                AS duration,
       (
       SELECT s.picture FROM samdb.songlist WHERE artist = s.artist AND album = s.album LIMIT 1)      AS picture,
       s.albumyear                                                                                    AS albumYear,
       SUM(IFNULL(s.votes, 0))                                                                        AS votes,
       IF(SUM(IFNULL(s.votes, 0)) = 0, 0,
          SUM(IFNULL(s.rating, 0) * s.votes) / SUM(IFNULL(s.votes, 0)))                               AS rating,
       COUNT(DISTINCT r.songID)                                                                       AS userVotes,
       IF(COUNT(DISTINCT r.songID) = 0,
          0,
          SUM(IFNULL(r.rating, 0)) / COUNT(DISTINCT r.songID)
           )                                                                                          AS userRating,
       UNIX_TIMESTAMP(MAX(s.date_played))                                                             AS dateLastPlayed,
       s.website                                                                                      AS website,
       s.label                                                                                        AS country,
       SUM(IFNULL(q.id, 0)) > 0                                                                       AS queued,
       MAX(s.date_played) > DATE_SUB(NOW(), INTERVAL 3 HOUR)                                          AS recent,
       SUM(s.count_played)                                                                            AS plays,
       COUNT(DISTINCT s.album)                                                                        AS albums,
       COUNT(s.id)                                                                                    AS songs,
       GROUP_CONCAT(DISTINCT s.genre)                                                                 AS genre,
       log10(SUM(IFNULL(s.rating, 0) * votes)) + LOG10(SUM(s.count_requested)) + LOG10(SUM(s.count_played)) AS popularity
FROM samdb.songlist s
     LEFT JOIN queuelist q
               ON q.songID = s.id
     LEFT JOIN samdb.song_rating r
               ON r.songID = s.ID AND r.userID = :userID
WHERE (ISNULL(NULLIF(:artist, '')) OR s.artist REGEXP :artist)
  AND (ISNULL(NULLIF(:country, '')) OR s.label = :country)
  AND (ISNULL(NULLIF(:album, '')) OR s.album REGEXP :album)
  AND (ISNULL(NULLIF(:title, '')) OR s.title REGEXP :title)
  AND (ISNULL(NULLIF(:genre, '')) OR s.genre REGEXP :genre)
  AND (ISNULL(NULLIF(:year, ''))
    OR IF(:year = 'new', date_added > DATE_SUB(NOW(), INTERVAL 30 DAY), s.albumyear REGEXP :year)
    )
GROUP BY artist, album
HAVING rating BETWEEN :minAvgRating AND :maxAvgRating
   AND userRating BETWEEN :minUserRating AND :maxUserRating
   AND (ISNULL(:since) OR dateLastPlayed <= DATE_SUB(NOW(), INTERVAL :since MONTH))
ORDER BY artist, album
