<?php
	session_start();
	session_destroy();
	if (isset($_SERVER['HTTP_COOKIE']))
	{
    	$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
	    foreach($cookies as $cookie)
	    {
	        $mainCookies = explode('=', $cookie);
	        $name = trim($mainCookies[0]);
	        setcookie($name, '', time()-1000);
	        setcookie($name, '', time()-1000, '/');
	    }
	}
	header("Location: login");
?>