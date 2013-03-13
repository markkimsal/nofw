
				<div class="tbox span<?=$this->span?>">
				<h2 class="tbox_head red_grad round_top"><?=$this->title?></h2>
					<div class="tbox_container">					
					<div class="block box_content round_bottom padding_20">
<?php
		if (is_object($this->output)) {
			$layout = $this->output->layout;
			if (is_object($layout)) {
				$layout->span=$span;
			}
			echo $this->output->toHtml($layout);
		} else {
			echo $this->getOutput();
		}
?>


					</div>
					</div>
				</div>
