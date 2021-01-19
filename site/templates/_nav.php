<nav class="navbar navbar-expand-lg bg-light">
	<div class="container-fluid">
		<a href="#" class="toggle-menu pr-1 pl-2" data-target="#dplus-nav">
			<i class="fa fa-bars fa-1-2x" aria-hidden="true"></i>
		</a>
		<a href="<?= $pages->get('/')->url; ?>" class=""  aria-label="homepage link">
			<img src="<?= $siteconfig->child('name=customer')->logo_large->height(50)->url; ?>" alt="">
		</a>
		<a class="font-weight-bold" href="<?= $pages->get('/')->url; ?>" aria-label="homepage link">
			<img src="<?= $appconfig->logo_small->url; ?>" width="30" height="30" alt="">
			DistributionPlus
		</a>
	</div>
</nav>
<?php include('./_nav-yt.php'); ?>
