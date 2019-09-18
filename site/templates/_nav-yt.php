<div id="yt-menu" class="bg-light">
	<div class="pl-3 pr-2">
		<p class="pt-1">
			Welcome, <?= $user->fullname; ?>
		</p>
		<nav>
			<?= $config->twig->render("nav/$user->dplusrole.twig", ['pages' => $pages, 'user' => $user]); ?>
		</nav>
	</div>
</div>
