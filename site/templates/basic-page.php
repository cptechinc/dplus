<?php include('./_head.php'); ?>
	<div class="jumbotron bg-dark page-banner rounded-0">
		<div class="container">
			<h1 class="display-4 text-light"><?= $page->get('pagetitle|headline|title') ; ?></h1>
		</div>
	</div>
	<div class='container page pt-3'>
		<?= $page->body; ?>
	</div>
<?php include('./_foot.php'); ?>
