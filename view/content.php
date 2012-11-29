<?php $content = selectContent($no); ?>
<?php if($content !== "" && $content->type === "content"):?>
		<div id="content">
			<div class="title"><?php echo $content->title; ?></div>
			<?php echo $content->content; ?>
			<div class="comment"></div>
		</div>
<?php endif; ?>