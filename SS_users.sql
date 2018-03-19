DROP TABLE IF EXISTS SS_users;
--
-- Table structure for table `SS_users`
--

CREATE TABLE SS_users (
  UserID int NOT NULL AUTO_INCREMENT,
  username varchar(255) NOT NULL,
  firstname varchar(50) DEFAULT NULL,
  lastname varchar(50) DEFAULT NULL,
  email varchar(50) NOT NULL,
  password varchar(255) NOT NULL,
  phone_number varchar(12) NOT NULL,
  PRIMARY KEY(UserID),
  UNIQUE KEY(email)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

SELECT * FROM SS_users;

#TEST
TRUNCATE TABLE SS_users;



