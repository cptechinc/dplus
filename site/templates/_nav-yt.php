<div id="yt-menu" class="bg-light">
	<div class="pl-3 pr-2">
		<p class="pt-1">
			Welcome, <?= $user->fullname; ?>
		</p>
		<nav>
			<?php $nav = empty($user->dplusrole) ? 'default' : $user->dplusrole; ?>
			<?= $config->twig->render("nav/$nav.twig", ['pages' => $pages, 'user' => $user]); ?>
		</nav>
	</div>
</div>
