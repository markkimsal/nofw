<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo associate_get('sitename', 'Nofw');?> Control Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="<?php echo m_turl();?>css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
		.wrapper {
		min-height: 100%;
		height: auto !important;
		height: 100%;
		margin: 0 auto -1.0em;
		}
		.footer, .push {
		height: 1.0em;
		clear:both;
		position: relative;
		}
    </style>
    <link href="<?php echo m_turl();?>css/bootstrap-responsive.css" rel="stylesheet">
    <link href="<?php echo m_turl();?>css/admin02-screen.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="<?php echo m_turl();?>ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo m_turl();?>ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo m_turl();?>ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo m_turl();?>ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="<?php echo m_turl();?>ico/apple-touch-icon-57-precomposed.png">
  </head>

  <body>

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="#"><?php echo associate_get('sitename', 'Control Panel');?></a>
          <div class="btn-group pull-right">
            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
              <i class="icon-user"></i> Username
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="#">Profile</a></li>
              <li class="divider"></li>
              <li><a href="#">Sign Out</a></li>
            </ul>
          </div>
          <div class="nav-collapse">
            <ul class="nav">
              <li><a href="#about">About</a></li>
              <li><a href="#contact">Contact</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="wrapper">
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span3">
          <div class="sidebar">
<?php foreach (associate_getMeA('admin_menu') as $_title => $_link): ?>
            <ul class="sidebar-nav">
			<li class="light red_grad"><a href="<?php echo $_link;?>">
				<div class="icon">
				<i class="icon-home icon-white"></i></div><?php echo $_title;?></a></li>
			</ul>
<?php endforeach; ?>
            <ul class="sidebar-nav">
			  <li class="closed light red_grad">
				<a href="<?php echo m_appurl('blog');?>">
				<div class="icon">
				<i class="icon-book icon-white"></i></div>Blog</a>
            	<ul class="subnav">
			  	<li><a href="#">Sub-Item</a></li>
            	</ul>
			</li>
            </ul>
          </div><!--/.well -->
        </div><!--/span-->
        <div class="span9">

			<!-- Main hero unit for a primary marketing message or call to action -->
			<?php if (Metrofw_Template::hasHandlers('template.sparkmsg')):?>
				<div class="hero-unit">
					<?php echo Metrofw_Template::parseSection('template.sparkmsg');?>
				</div>
			<?php endif;?>

			<div class="row-fluid">

			<?php if (Metrofw_Template::hasHandlers('template.main')):?>
					<?php echo Metrofw_Template::parseSection('template.main');?>
			<?php endif;?>

	      	</div><!--/row-->
      	</div><!--/.span9-->

     </div><!--/row-->
    </div><!--/.fluid-container-->
<div class="push"></div>
     </div><!--/wrapper-->

    <div class="footer">
    <div class="container-fluid">
      <div class="row-fluid">
      <div class="span3">
        <p>&copy; 2012</p>
	  </div>
      <div class="span9">
        <p>uses Twitter Bootstrap</p>
	  </div>
    </div>
    </div>

    <!-- Le javascript
    ================================================== -->
    <script src="<?php echo m_turl();?>js/jquery.min.js"></script>
    <script src="<?php echo m_turl();?>js/bootstrap.js"></script>
  </body>
</html>
