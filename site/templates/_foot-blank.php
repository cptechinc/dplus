		<?= $config->twig->render('util/ajax-modal.twig'); ?>
		<?= $config->twig->render('util/loading-modal.twig'); ?>
		<?= $config->twig->render('shared/image-modal.twig'); ?>
		<?php foreach($config->scripts->unique() as $script) : ?>
			<script src="<?= $script; ?>"></script>
		<?php endforeach; ?>
		<script>
			var api    = <?= json_encode($config->js('api')); ?>;
			var config = <?= json_encode($config->js('config')); ?>;
			var agent  = <?= json_encode($config->js('agent')); ?>;
			var user   = <?= json_encode($config->js('user')); ?>;

			<?php foreach ($config->js('vars') as $var => $data) : ?>
				let <?= $var; ?> = <?= json_encode($data); ?>;
			<?php endforeach; ?>
		</script>
		<?php if ($page->js) : ?>
			<script>
				<?= $page->js; ?>
			</script>
		<?php endif; ?>
	</body>
</html>
