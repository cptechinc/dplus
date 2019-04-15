		<?php include ('./_ajax-modal.php'); ?>
		<?php foreach($config->scripts->unique() as $script) : ?>
			<script src="<?= $script; ?>"></script>
		<?php endforeach; ?>
	</body>
</html>
