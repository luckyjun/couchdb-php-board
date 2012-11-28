	<?php 
		if(array_key_exists("no",$_GET)){
			$content = selectContent($_GET["no"]);
			if($content->type === "content"){
	?>
				<div id="content">
					<div id="title">
						<?php echo $content->title; ?>
					</div>
					<?php echo $content->content; ?>
				</div>
	<?php
			}			
		}
	?>