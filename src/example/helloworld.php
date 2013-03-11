<?php


class Example_Helloworld {

	public function output(&$request) {
		if ($request->isAjax) {
			$output = array('msg'=>'Hello, World.');
			echo json_encode($output);
		} else {
			associate_iCanHandle('output', 'example/footer.php');
			echo "Hello World. <br/>\n";
		}
	}
}
