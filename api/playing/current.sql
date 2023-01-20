SELECT s.id,
       s.duration,
       s.artist,
       s.album,
       s.title,
       s.label                                          AS country,
       s.albumyear                                      AS albumYear,
       s.website,
       s.buycd,
       s.picture,
       s.votes,
       s.rating,
       UNIX_TIMESTAMP(h.date_played)                    AS dateLastPlayed,
       IFNULL(rh.username, 'HAL')                       AS requester,
       IFNULL(rl.name, '')                              AS msgname,
       IFNULL(rl.msg, '')                               AS msg,
       UNIX_TIMESTAMP() - UNIX_TIMESTAMP(h.date_played) AS sinceStart,
       IFNULL(r.rating, 0)                              AS userRating,
       UNIX_TIMESTAMP()                                 AS now
FROM samdb.historylist h
     INNER JOIN samdb.songlist s
                ON s.id = h.songID
     LEFT JOIN  samdb.request_history rh
                ON rh.requestID = h.requestID
                    AND rh.songID = h.songID
     LEFT JOIN  samdb.requestlist rl
                ON rl.ID = h.requestID
                    AND rl.songID = h.songID
     LEFT JOIN  samdb.song_rating r
                ON r.songID = h.songID AND r.userID = :userID
WHERE (IFNULL(:before, 0) = 0 OR h.date_played <= FROM_UNIXTIME(:before))
ORDER BY h.date_played DESC, h.id DESC
LIMIT :offset, :limit
