DROP TABLE IF EXISTS SS_media;
--
-- Table structure for table `SS_media`
--
#NOTE: media is restricted to 16,777,215 bytes (16MB)
CREATE TABLE SS_media (
  MediaID int NOT NULL AUTO_INCREMENT,
  UserID int NOT NULL,
  media MEDIUMBLOB DEFAULT NULL,
  mimetype varchar(255) DEFAULT NULL,
  PRIMARY KEY(MediaID),
  FOREIGN KEY(UserID) REFERENCES SS_users(UserID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

SELECT * FROM SS_media;

#link: https://stackoverflow.com/questions/23854070/pdo-insert-image-into-database-directly-always-inserting-blob-0b
#link: https://stackoverflow.com/questions/8920007/read-blob-from-mysql-using-php-pdo
#link: https://stackoverflow.com/questions/5999466/php-retrieve-image-from-mysql-using-pdo
