DROP TABLE IF EXISTS SS_admins;
--
-- Table structure for table `SS_admins`
--

CREATE TABLE SS_admins (
  UserID int NOT NULL AUTO_INCREMENT,
  username varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  PRIMARY KEY(UserID),
  UNIQUE KEY(username)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

SELECT * FROM SS_admins;

#TEST
TRUNCATE TABLE SS_admins;



