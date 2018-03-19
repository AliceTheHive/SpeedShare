DROP TABLE IF EXISTS SS_images;

CREATE TABLE SS_images (
  ImageID int NOT NULL AUTO_INCREMENT,
  username varchar(255) NOT NULL,
  filename varchar(255) NOT NULL,
  filetype varchar(50) NOT NULL,
  PRIMARY KEY(ImageID),
  UNIQUE KEY(username, filename)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

SELECT * FROM SS_images;

#TEST
# TRUNCATE TABLE SS_images;



