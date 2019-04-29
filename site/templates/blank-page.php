<?php include('./_head-blank.php'); ?>
	<div class='container page pt-3'>
		<?php if ($page->show_title) : ?>
			<h2><?= $page->title; ?></h2>
		<?php endif; ?>
		<?= $page->body; ?>
	</div>
<?php include('./_foot-blank.php'); ?>
