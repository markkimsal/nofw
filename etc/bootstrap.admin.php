<?php

associate_iCanHandle('master', 'nofw/master.php');

associate_iCanHandle('analyze', 'example/analyzer.php');
associate_iCanHandle('analyze',  'metrofw/router.php');

associate_iCanHandle('resources', 'metrodb/connector.php');
//associate_iCanHandle('resources', 'metroadmin/resources.php');

associate_iCanHandle('output', 'metrofw/template.php');

associate_iAmA('session',  'metrou/sessiondb.php');
associate_iAmA('user',     'metrou/user.php');
associate_iAmA('request',  'metrofw/request.php');
associate_iAmA('router',   'metrofw/router.php');
associate_iAmA('form',     'metroui/form.php');

associate_set('default.dsn', 'mysql://root:@localhost/cognifty_test');
associate_set('env', 'dev');

associate_set('template_basedir', 'templates/');
associate_set('template_baseuri', 'templates/');

include('src/metroadmin/bootstrap.php');
