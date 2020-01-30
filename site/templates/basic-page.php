<?php
	$parents = $page->parents();
?>
<?php include('./_head.php'); ?>
	<div class="jumbotron bg-dark page-banner rounded-0 mb-3">
		<div class="container">
			<h1 class="display-4 text-light"><?= $page->get('pagetitle|headline|title'); ?></h1>
		</div>
	</div>
	<div class="container">
		<?php if ($page->show_breadcrumbs) : ?>
			<nav aria-label="breadcrumb rounded-0">
				<ol class="breadcrumb">
					<?php foreach ($parents as $parent) : ?>
						<li class="breadcrumb-item">
							<i class="fa fa-list" aria-hidden="true"></i>
							<a href="<?= $parent->url; ?>"><?= $parent->title; ?></a>
						</li>
					<?php endforeach; ?>
					<?php if ($page->has('title_previous')) : ?>
						<li class="breadcrumb-item">
							<a href="<?= $page->url; ?>"><?= $page->title_previous; ?></a>
						</li>
					<?php endif; ?>
					<li class="breadcrumb-item active" aria-current="page"><?= $page->get('pagetitle|headline|title'); ?></li>
				</ol>
			</nav>
		<?php endif; ?>
	</div>
	<div class='container page <?= ($page->show_breadcrumbs) ? 'pt-3' : ''; ?>'>
		<?= $page->body; ?>
	</div>
<?php include('./_foot.php'); ?>
