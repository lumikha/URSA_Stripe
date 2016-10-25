<?php
date_default_timezone_set("Asia/Manila");
function GUID(){

	date_default_timezone_set("Asia/Manila");
	$t = microtime(true);
	$micro = sprintf("%06d",($t - floor($t)) * 1000000);
	$d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );

	return $d->format("YmdHisu");
}
echo GUID
?>