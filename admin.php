<?php

$nofw_start = microtime(true);
include ('src/nofw/associate.php');

if(!include('etc/bootstrap.admin.php')) {
	echo "please setup your etc/bootstrap.admin.php file.";
	exit();
}


$assoc = Nofw_Associate::getAssociate();

while ($svc = $assoc->whoCanHandle('master')) {
	$svc->runMaster();
}

