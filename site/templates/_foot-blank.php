		<?= $config->twig->render('util/ajax-modal.twig'); ?>
		<?php foreach($config->scripts->unique() as $script) : ?>
			<script src="<?= $script; ?>"></script>
		<?php endforeach; ?>
		<?php if ($page->js) : ?>
			<script>
				<?= $page->js; ?>
			</script>
		<?php endif; ?>
	</body>
</html>
