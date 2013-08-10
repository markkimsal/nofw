nofw
====

Nofw is a meta-framework or a micro-framework focused on dependency injection, event driven architecture, lazy-loading, and blending library code with framework logic.  Nofw works with dependency managers like PHP composer and bower for nodejs.

With most frameworks, configuration is an afterthought.  This leads to hugely complex libraries which are required to be loaded every request to handle every possible situation.  What if you could write controllers that were actually part of the framework and could redirect the flow to new sections of the framework at runtime?

http://markkimsal.github.io/nofw/

lifecycle
====
By default, there are 6 portions to the request lifecycle:
 * analyze
 * resources
 * authenticate
 * process
 * output
 * hangup

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
Sometimes you need to use an object in more than one place (duh).  These are 'things' in nofw.  If you want to have a standard user class, in the etc/bootstrap.php file simply add:
```php
associate_IAmA('user', 'myuser/user_model.php');
```

In other parts of your code, you can retreive this user with:
```php
$user = associate_getMeA('user');
```

The same technique can be used for DB handles or wrapper objects.  Experiment loading up 'things' in the 'resources' lifecycle.

You can pass constructor arguments to things as well:

```php
associate_IAmA('db', 'mylibs/mysql.php');
$db1 = associate_getMeA('db', 'mysql://root@localhost/mydb');
$db2 = associate_getMeA('db', 'mysql://root@localhost/otherdb');
```

You can also request a clone of an object with:
```php
associate_IAmA('user', 'myuser/user.php');
$loggedInUser = associate_getMeA('user');
$otherUser    = associate_getMeANew('user');
```

The $otherUser will be a clone (in PHP5) of the same object used for $loggedInUser.  Objects with different constructor arguments are always different, they are cached with the name of the file and an SHA1 sum of the constructor arguments.

undefined things
======
You can request a thing that has not been defined.  If you do this you will receive a new Nofw_Proto object reference.  This can be usefull for rapidly prototyping interconnections between libraries.  Starting with a bare object, you can inject a real class definition *later*.  Any function call against a Nofw_Proto will return itself and log the call against an undefined thing with any "logger" that you define.

```php
$cart = _getMeA("shopping_cart");
$cart->addItem("abc", 123)->
       retotal()->
       updateInventory()->
       save();
```

Then, after a few iterations of development or complete launches, you can add a full class definition if you need to expand the capabilities of the thing.
```php
associate_IamA('shopping_cart', 'myecommerce/cart.php');
```

flags / settings
======
Setting and getting global values can be done with the associate_get()/associate_set() functions.  This can be handy when you set database connection information in the bootstrap, then load that information in your database abstraction library.  Other ideas can be copyright inforation, version strings, and template variables.

custom lifecycles
======
There's nothing stopping your from creating your own chain of command with the same lifecycles used in the master process running.

```php
		associate_iCanHandle('Top Menu', 'mytemplate/menu.php');
		associate_iCanHandle('Bottom Footer', 'mytemplate/menu.php');
		if (isset($_SESSION['sparkMessages'])) {
			associate_iCanHandle('Spark Messages', 'mytemplate/sparkmsg.php');
		}

		//later...

		$associate = Nofw_Associate::getAssociate();
		$request = $associate->getMeA('request');
		$output = '';
		while ($svc =  $associate->whoCanHandle('Top Menu')) {
			$output .= $svc->template($request, 'Top Menu');
		}
		while ($svc =  $associate->whoCanHandle('Spark Messages')) {
			$output .= $svc->template($request, 'Spark Messages');
		}
		while ($svc =  $associate->whoCanHandle('Main Content')) {
			$output .= $svc->template($request, 'Main Content');
		}
		while ($svc =  $associate->whoCanHandle('Bottom Footer')) {
			$output .= $svc->template($request, 'Bottom Footer');
		}

		return $output."\n";
```

I build large sites, micro-frameworks aren't for me
=====
Sure, so you should know how important it is to control resources on every page hit.  Micro-frameworks really shine on large projects where every resource must be optimized for every hit.  

composer and bower support
=====
Both composer and bower are configured to store dependencies in the local/ directory.  Bower is simpler to configure for dependencies that don't have composer.json files in their code.  Composer is nicer for PHP projects that support composer because of the automatic auto-loading file that's generated for you.  Pick either or mix and match.
