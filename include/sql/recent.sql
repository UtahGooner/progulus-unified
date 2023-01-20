SELECT h.ID,
       s.id                                                             AS songID,
       s.artist,
       s.album,
       s.title,
       s.duration,
       s.rating,
       s.votes,
       h.date_played,
       r.t_stamp                                                        AS date_requested,
       (UNIX_TIMESTAMP(h.date_played) - UNIX_TIMESTAMP(r.t_stamp)) / 60 AS wait,
       rh.username,
       h.listeners
FROM historylist h
     INNER JOIN songlist s
                ON s.id = h.songID
     LEFT JOIN requestlist r
               ON r.id = h.requestid
     LEFT JOIN request_history rh
               ON rh.requestID = r.id
WHERE (IFNULL(:me, 0) = 0 OR rh.userid = :me)
ORDER BY h.id DESC
LIMIT :start, :limit
