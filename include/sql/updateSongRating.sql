UPDATE samdb.songlist
SET rating = (SELECT AVG(rating) FROM song_rating WHERE songID = :songID),
    votes = (SELECT COUNT(*) FROM song_rating WHERE songID = :songID)
WHERE ID = :songID
