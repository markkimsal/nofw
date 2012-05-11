nofw
====

No framework framework

setup
=====
Copy etc/bootstrap.php.txt to etc/bootstrap.php
Add the following line to etc/bootstrap.php

  associate_iCanHandle('output', 'mypage/helloworld.php');

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
What happens if you put this in the output function of hello world.
	associate_iCanHandle('output', 'mypage/footer.php');
