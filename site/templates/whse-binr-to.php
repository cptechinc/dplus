<?php
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$tobin = $warehouse->validate_bin($input->get->text('tobin')) ? $input->get->text('tobin') : '';

	if (!empty($tobin)) {
		include('./whse-binr.php');
	} else {
		$page->body =  $config->twig->render('warehouse/binr/direction/bin-form.twig', ['page' => $page, 'inputname' => 'tobin']);
		$page->body .= $config->twig->render('warehouse/inventory/bins-modal.twig', ['warehouse' => $warehouse]);

		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		$config->scripts->append(hash_templatefile('scripts/warehouse/shared.js'));
		$config->scripts->append(hash_templatefile('scripts/warehouse/binr-to.js'));

		include __DIR__ . "/basic-page.php";
	}
