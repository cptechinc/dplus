<div id="yt-menu" class="bg-light">
	<div class="pl-3 pr-2">
		<p class="pt-1">
			Welcome, <?= $user->fullname; ?>
		</p>
		<nav>
			<ul class="nav flex-column">
				<li class="nav-item">
					<a class="nav-link" href="<?= $pages->get('template=dashboard')->url; ?>"><?= $pages->get('template=dashboard')->title; ?></a>
				</li>
				<?php foreach ($pages->get('/')->children('template=dplus-menu|menu') as $child) : ?>
					<li class="nav-item">
						<a class="nav-link" href="<?= $child->url; ?>"><?= $child->title; ?></a>
					</li>
				<?php endforeach; ?>
				<li>
					<form action="<?= $pages->get('template=menu')->url; ?>" class="mt-1 mb-3 allow-enterkey-submit">
						<label for="nav-menu-search">Dplus Code</label>
						<div class="input-group mb-3">
							<input type="text" class="form-control" name="q" id="nav-menu-search">
							<div class="input-group-append">
								<button type="submit" class="btn btn-primary">Go</button>
							</div>
						</div>
					</form>
				</li>
				<li class="nav-item bg-danger">
					<a class="nav-link text-white" href="<?= $pages->get('/user/redir/')->url."?action=logout"; ?>">Logout <i class="fa fa-sign-out" aria-hidden="true"></i></a>
				</li>
			</ul>
		</nav>
	</div>
</div>
