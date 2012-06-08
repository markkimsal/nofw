<?php


class Example_Helloworld {

	public function output(&$request) {
		$user = associate_getMeA('user');
		if ($request->isAjax) {
			$output = array('msg'=>'Hello, World.', 'output'=>$user->sayGoodbye());
			echo json_encode($output);
		} else {
			associate_iCanHandle('output', 'example/footer.php');
			echo "Hello World. <br/>\n";
			echo $user->sayGoodbye(). "<br/>\n";
		}
	}
}