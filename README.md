nofw
====

With most frameworks, configuration is an afterthought.  This leads to hugely complex libraries which are required to be loaded every request to handle every possible situation.  What if you could write controllers that were actually part of the framework and could redirect the flow to new sections of the framework at runtime?

lifecycle
====
As it stands now, there are 6 portions to the request lifecycle:
 * analyze();
 * resources();
 * authenticate();
 * process();
 * output();
 * hangup();

Each part of the lifecycle functions as a signal/slot event mechanism.  You add your libraries to the lifecycle by telling the associate you can handle a part of a lifecycle.

```php
  associate_iCanHandle('hangup', 'mytools/recordexecutiontime.php');
```

Inside the specified file should be a class named Mytools_Recordexecutiontime and a function called hangup();

If, during the process of certain requests, you decide that you want to completely alter the execution path you can *own* part of a lifecycle with associate_iCanOwn( $cycle, $file).  This will erase all previously identified slots for that part of the lifecycle.

sounds dumb
==========
Indeed, and perhaps it is dumb.  Maybe you are wondering where all the nifty libraries are that force you to do things a certain way.  Those already exist and are just waiting for you to hook them in!  You can chain in an entire Zend Framework application with one class that wraps up $application->bootstrap()->run().  What's the point of that, you say?  It gives you a starting point to dissect your existing application and inject resource-light responses when needed.

setup
=====
Copy etc/bootstrap.php.txt to etc/bootstrap.php
Add the following line to etc/bootstrap.php

```php
  associate_iCanHandle('output', 'mypage/helloworld.php');
```

Create a folder in local called 'mypage'.
Create a file called 'helloworld.php' in 'mypage'.

Add this to the helloworld.php file

```php
<?php

class Mypage_Helloworld {

	public function output(&$request) {
		echo "Hello World.";
	}
}
```

advanced
=======
What happens if you put this in the output function of hello world?
```php
	associate_iCanHandle('output', 'mypage/footer.php');
```

things
======
Sometimes you need to use an object in more than one place (duh).  These are 'things' in nofw.  If you want to have a standard user class, in the etc/bootstrap.php file simple add:
```php
associate_IAmA('user', 'myuser/user_model.php');
```

In other parts of your code, you can retreive this user with:
```php
$user = associate_getMeA('user');
```

The same technique can be used for DB handles or wrapper objects.  Experiment loading up 'things' in the 'resources' lifecycle.

I build large sites, this isn't for me
=====
Sure, so you should know how important it is to control resources on every page hit.  Do you know how your existing framework can handle large modifications to the workflow?  What if you had to add on API functionality, where the requests don't have cookies (and therefore sessions)?  When you're outputting JSON data do you need to load the entire templating library just to turn it off so you can send raw JSON?  What about a public RSS feed that is cached, can you avoid loading user and database libraries at all on those types of requests?  Does your framework offer customization at every level?
