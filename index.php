<?php 
	if(!isset($_SESSION['user_now_id'])) { 
		header("Location: login");
	} else {
		if(isset($_SESSION['user_now_access_level']) == "Customer") { 
			header("Location: account");
		} else {
			header("Location: customer");
		}
	}
?>