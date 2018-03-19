<?php require_once './includes/reg_conn.php';
//Manually clear session
session_start();
$_SESSION=array();
session_destroy();
require './includes/header.php'; ?>
    <!-- Maxwell Crawford -->
	<h5> Welcome to your new favorite uploader! </h5>
	<br>
	<section>
		<h3><em> Why use this site?...</em><br>
		SpeedShare is useful for nearly instant image uploads <br> 
		and sharing through simple URLs and micro cloud storage.</h3>
	</section>
	<?php include './includes/footer.php'; ?>