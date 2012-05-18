<!DOCTYPE html>
<html lang="en">
  <head>
	<meta charset="utf-8">
	<title><?php echo sitename();?></title>
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
	<!--[if lt IE 9]>
	  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

	<!-- Le styles -->
	<link href="<?php echo m_turl(); ?>css/bootstrap.min.css" rel="stylesheet">
	<link href="<?php echo m_url(); ?>media/shared_css/system.css" rel="stylesheet">
	<link href="<?php echo m_url(); ?>media/shared_css/form.css" rel="stylesheet">
	<link href="<?php echo m_turl(); ?>css/webapp-screen.css" rel="stylesheet">

	<style type="text/css">
	  body {
		padding-top: 60px;
	  }
	</style>

	<!-- Le fav and touch icons -->
	<link rel="shortcut icon" href="images/favicon.ico">
	<link rel="apple-touch-icon" href="images/apple-touch-icon.png">
	<link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">
	<link rel="shortcut icon"
		type="image/ico" />
  </head>
  <body>
	<div class="topbar">
		<div class="fill">
			<div class="container">
				<div class="row">
				<div class="span9">
				<a class="brand" href="<?php echo m_url();?>"><?php echo sitename();?></a>
				<?php echo Metrofw_Template::parseSection('template.topmenu');  ?>
				</div>

				<div class="span2 offset1" style="padding-top:5px;">
				<?php echo Metrofw_Template::parseSection('template.toplogin');  ?>
				</div>
				</div>
			</div>
		</div>

		<div class="container" id="site-wrap">
			<!-- Main hero unit for a primary marketing message or call to action -->
			<?php if (Metrofw_Template::hasHandlers('template.sparkmsg')):?>
				<?php echo Metrofw_Template::parseSection('template.sparkmsg');?>
			<?php endif;?>

			<div class="row">
			<div class="span14 offset1">
				<?php echo Metrofw_Template::parseSection('template.main');?>
			</div>
			</div>

			<div class="site-footer">
			</div>
		</div> <!-- /container -->

  </body>
</html>
