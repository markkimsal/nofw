<?
	$uri = $_SERVER['REQUEST_URI'];
	//remove the last index.php
	define('ROOT_URL', substr($uri, 0, -1 * strlen('admin/')));
	header('Location: '.ROOT_URL.'admin.php');
?>
