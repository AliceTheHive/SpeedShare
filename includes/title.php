<?php
    //Author: Maxwell Crawford
	$title = '';
	$title = $title . ' - ' . basename($_SERVER['SCRIPT_FILENAME'], '.php');
	$title = str_replace('_', ' ', $title);
	if(strpos($title,'index') !== false){
		$title = ' - Home';
		}
	$title = ucwords($title);