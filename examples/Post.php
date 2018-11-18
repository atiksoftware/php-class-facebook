<?php 

	include "../vendor/autoload.php";

	use \Atiksoftware\Facebook\Facebook;

	$fb = new Facebook();

	try { 
		$fb->login("username","password");
	} catch (Exception $e) {
		echo $e->getMessage(); 
	}
	
	try { 
		$fb->post("pageid","message_text","image_file_path");
	} catch (Exception $e) {
		echo $e->getMessage(); 
	} 
 
	
