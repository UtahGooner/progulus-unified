INSERT INTO samdb.song_rating (songID, userID, rating, username)
VALUES (:songID, :userID, :rating, :username)
ON DUPLICATE KEY UPDATE rating = :rating
