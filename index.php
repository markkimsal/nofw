<?php

include ('src/corefw/associate.php');

if(!include('etc/bootstrap.php')) {
	echo "please setup your etc/bootstrap.php file.";
	exit();
}


$assoc = Corefw_Associate::getAssociate();

include ('src/corefw/master.php');
while ($svc = $assoc->whoCanHandle('master')) {
	$svc->runMaster();
}

