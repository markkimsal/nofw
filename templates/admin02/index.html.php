<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="application/xhtml+xml;charset=utf-8" />
    <title><?php echo associate_get('sitename', 'Nofw');?> Control Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="" />
    <meta name="author" content="" />

    <!-- Le styles -->
    <link href="<?php echo m_turl();?>css/bootstrap.css" rel="stylesheet" />
    <style type="text/css">
		.wrapper {
		min-height: 100%;
		height: auto !important;
		height: 100%;
		margin: 0 auto -1.4em;
		}
		.footer, .push {
		height: 1.4em;
		clear:both;
		position: relative;
		}
html, body {
height: 100.0%;
font-size:11pt;
}
body {
line-height:1.8em;
padding-top:58px;
}

    </style>
    <link href="<?php echo m_turl();?>css/bootstrap-responsive.css" rel="stylesheet" />
    <link href="<?php echo m_turl();?>css/admin02-screen.css" rel="stylesheet" />

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="<?php echo m_turl();?>ico/favicon.ico" />
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo m_turl();?>ico/apple-touch-icon-144-precomposed.png" />
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo m_turl();?>ico/apple-touch-icon-114-precomposed.png" />
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo m_turl();?>ico/apple-touch-icon-72-precomposed.png" />
    <link rel="apple-touch-icon-precomposed" href="<?php echo m_turl();?>ico/apple-touch-icon-57-precomposed.png" />
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
	 <?php $user = $req->getUser(); ?>
	 <?php if (!$user->isAnonymous()): ?>
          <div class="btn-group pull-right">
            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
	    <i class="icon-user"></i><?php echo $user->getUsername(); ?>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a target="_blank" href="<?php echo m_url();?>">View Site</a></li>
              <li class="divider"></li>
              <li><a href="#">Profile</a></li>
              <li class="divider"></li>
              <li><a href="<?php echo m_appurl('dologout');?>">Sign Out</a></li>
            </ul>
          </div>
	  <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="wrapper">
    <div class="container">
      <div class="row">
        <div class="span3 nav-collapse">
          <div class="sidebar">
			<?php if (! Metrofw_Template::hasHandlers('template.adminmenu')):?>
				&nbsp;
			<?php endif; ?>
			<?php echo Metrofw_Template::parseSection('template.adminmenu');?>
<!--
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
-->
          </div><!--/.well -->
        </div><!--/span-->
        <div class="span9">

			<!-- Main hero unit for a primary marketing message or call to action -->
			<?php if (Metrofw_Template::hasHandlers('template.sparkmsg')):?>
				<div class="alert alert-danger">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<?php echo Metrofw_Template::parseSection('template.sparkmsg');?>
				</div>
			<?php endif;?>

					<?php echo Metrofw_Template::parseSection('template.items');?>

      	</div><!--/.span9-->

     </div><!--/row-->
    </div><!--/.container-->
<div class="push"></div>
     </div><!--/wrapper-->

    <div class="footer">
    <div class="container">
      <div class="row">
      <div class="span1">
        <p>&copy; 2012</p>
	  </div>
      <div class="span9">
        <p>uses Twitter Bootstrap | 
		<?php echo Metrofw_Template::parseSection('template.timer');?>
		</p>
	  </div>
    </div>
    </div>

    <!-- Le javascript
    ================================================== -->
    <script src="<?php echo m_turl();?>js/jquery.min.js"></script>
    <script src="<?php echo m_turl();?>js/bootstrap.js"></script>
	<?php echo Metrofw_Template::parseSection('template.extrajs');?>
  </body>
</html>
