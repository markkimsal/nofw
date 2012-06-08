<?php

$nofw_start = microtime(true);
include ('src/nofw/associate.php');

if(!include('etc/bootstrap.php')) {
	echo "please setup your etc/bootstrap.php file.";
	exit();
}


$assoc = Nofw_Associate::getAssociate();

while ($svc = $assoc->whoCanHandle('master')) {
	$svc->runMaster();
}

