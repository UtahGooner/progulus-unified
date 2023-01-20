SELECT s.id,
       s.artist,
       s.album,
       s.title,
       s.duration,
       s.picture,
       s.trackno                                                      AS track,
       s.albumyear                                                    AS albumYear,
       IFNULL(s.votes, 0)                                             AS votes,
       IF(IFNULL(s.votes, 0) = 0, 0,
          (IFNULL(s.rating, 0) * s.votes) / s.votes)                  AS rating,
       r.rating                                                       AS userRating,
       IF(s.count_played = 0, NULL, UNIX_TIMESTAMP(s.date_played)) AS dateLastPlayed,
        s.website,
       s.label as country,
       IFNULL(q.id, 0) > 0                                            AS queued,
       s.date_played > DATE_SUB(NOW(), INTERVAL 3 HOUR)               AS recent,
       s.count_played                                                 AS plays,
       s.genre                                                        AS genre,
       log10(s.rating * votes) + log10(s.count_requested) + log10(s.count_played) AS popularity
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
  AND IFNULL(s.rating, 0) BETWEEN :minAvgRating AND :maxAvgRating
  AND IFNULL(r.rating, 0) BETWEEN :minUserRating AND :maxUserRating
  AND (ISNULL(:since) OR if(:since = 0, s.count_played = 0, s.date_played <= DATE_SUB(NOW(), INTERVAL :since MONTH)))
ORDER BY artist, albumyear, album, trackno
LIMIT 500
