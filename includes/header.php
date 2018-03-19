<?php session_start();
include 'title.php';?>
<!DOCTYPE html>
<html lang="en">
<head>
	<!--Author: Maxwell Crawford -->
	<title>SpeedShare <?php if(isset($title)) {echo "$title";} ?></title>
	<meta charset="utf-8">
    <!--<link rel="stylesheet" type="text/css" href="./styles/main.css">-->
    <link rel="stylesheet" type="text/css" href="./styles/new.css">

	<!-- Favicon/logo code, courtesy of realfavicongenerator.net -->
	<link rel="apple-touch-icon" sizes="180x180" href="./images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="./images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="./images/favicon-16x16.png">
	<link rel="manifest" href="./images/manifest.json">
	<link rel="mask-icon" href="./images/safari-pinned-tab.svg" color="#5bbad5">
	<link rel="shortcut icon" href="./images/favicon.ico">
	<meta name="msapplication-config" content="./images/browserconfig.xml">
	<meta name="theme-color" content="#33afff">
</head>
<body>
	<header>
		<span class="banner_h">
            <img class="banner_img" src="images/topbanner.jpg" alt="Banner" title="SpeedShare Banner" />
            <img class="sm_logo" src="images/logo_sm3.png" alt="SpeedShare!" title="SpeedShare!"/>
		</span>
		<?php $currentPage = basename($_SERVER['SCRIPT_FILENAME']);?>
		<?php if(($currentPage == 'admin_menu.php') || ($currentPage == 'admin_message.php')){$currname = 'Leave Admin';} elseif (isset($_SESSION['username'])){$currname = $_SESSION['username']; $logouttext = 'Logout: ' . $currname;} else{$currname = 'Sign In/Register';}?>
        <!-- NOTE: sign_in.php uses secure_conn.php to ensure HTTPS;-->
        <!--Also, style of Sign In btn dynamically changes with session var-->
        <!--Lastly, if already signed in, logout and go to home -->
		<nav>
			<ul id="navbar">
                <li><a <?php if(($currentPage == 'admin_menu.php') || ($currentPage == 'admin_message.php')){echo 'href="admin_menu.php"';} elseif (isset($_SESSION['username'])){echo 'href="index_auth.php"';} else{echo 'href="index.php"';}?><?php if (($currentPage == 'index.php') || ($currentPage == 'index_auth.php') || ($currentPage == 'admin_menu.php') || ($currentPage == 'admin_message.php')) {echo ' id="homehere"';} ?>><?php if(($currentPage == 'admin_menu.php') || ($currentPage == 'admin_message.php')){echo 'Admin Menu';} else{echo 'Home';}?></a></li>
			    <?php if ((isset($_SESSION['username']) && ($currentPage != 'admin_menu.php') && ($currentPage != 'admin_message.php'))){echo '<li><a href="upload.php"';} if ($currentPage == 'upload.php') {echo ' id="here"';} if ((isset($_SESSION['username']) && ($currentPage != 'admin_menu.php') && ($currentPage != 'admin_message.php'))){echo '>Upload</a></li>';} ?>
                <?php if ((isset($_SESSION['username']) && ($currentPage != 'admin_menu.php') && ($currentPage != 'admin_message.php'))){echo '<li><a href="storage.php"';} if ($currentPage == 'storage.php') {echo ' id="here"';} if ((isset($_SESSION['username']) && ($currentPage != 'admin_menu.php') && ($currentPage != 'admin_message.php'))){echo '>Storage</a></li>';} ?>
                <li style="float:right;<?php if (isset($_SESSION['username'])){echo '; font-size: 18px; width: 15%;';}?>"><a class="active" <?php if(isset($_SESSION['username'])){echo ' href="index.php"';} elseif(($currentPage == 'admin_menu.php') || ($currentPage == 'admin_message.php')){echo 'href="index.php"';} else{echo ' href="sign_in.php"';}?><?php if ($currentPage == 'sign_in.php') {echo ' id="reghere"';} ?>><?php if(($currentPage == 'admin_menu.php') || ($currentPage == 'admin_message.php')){echo "$currname";} elseif(isset($_SESSION['username'])){echo "$logouttext";} else{echo "$currname";}?></a></li>
			</ul>
		</nav>
        <h2>SpeedShare! -- <em>Quick -n- Simple Image Hosting.</em></h2>
	</header>