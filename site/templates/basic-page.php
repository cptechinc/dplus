<?php
	$parents = $page->parents();
?>
<?php include('./_head.php'); ?>
	<?php if (100 == 1) :?>
		<div class="jumbotron bg-dark page-banner rounded-0 mb-3">
			<div class="container">
				<h1 class="display-4 text-light"><?= $page->get('pagetitle|headline|title'); ?></h1>
			</div>
		</div>
	<?php endif; ?>
	<div class="container mt-3">
		<?php if ($page->show_breadcrumbs) : ?>
			<?= $config->twig->render('util/bread-crumbs.twig', ['page' => $page]); ?>
		<?php endif; ?>
	</div>
	<div class='container page <?= ($page->show_breadcrumbs) ? 'pt-3' : ''; ?>'>
		<?= $page->body; ?>
	</div>
<?php include('./_foot.php'); ?>
