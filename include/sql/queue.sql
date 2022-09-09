SELECT s.id,
       s.duration,
       s.artist,
       s.album,
       s.title,
       s.label                    AS country,
       s.albumyear,
       s.website,
       s.buycd,
       s.picture,
       s.votes,
       s.rating,
       NULL                       AS date_played,
       IFNULL(rh.username, 'HAL') AS requester,
       IFNULL(rl.name, '')        AS msgName,
       IFNULL(rl.msg, '')         AS message,
       NULL                       AS sinceStart,
       IFNULL(r.rating, 0)        AS userRating
FROM queuelist q
     INNER JOIN songlist s
                ON s.id = q.songID
     LEFT JOIN  request_history rh
                ON rh.requestID = q.requestID
                    AND rh.songID = q.songID
     LEFT JOIN  requestlist rl
                ON rl.ID = q.requestID
                    AND rl.songID = q.songID
     LEFT JOIN  samdb.song_rating r
                ON r.songID = q.songID AND r.userID = :userID
ORDER BY q.sortID
