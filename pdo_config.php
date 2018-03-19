<?php
	define(DBCONNSTRING,'mysql:host=127.0.0.1;dbname=mc1838');
	define(DBUSER, 'mc1838');
	define(DBPASS,'FQUp7XWyX');
	try {
		$conn= new PDO(DBCONNSTRING, DBUSER, DBPASS);
	} catch (PDOException $e) {
        echo '<h3>We are unable to process your request at this time.</h3>';
	}
?>