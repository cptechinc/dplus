<?php
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$frombin = $warehouse->validate_bin($input->get->text('frombin')) ? $input->get->text('frombin') : '';

	if (!empty($frombin)) {
		include('./whse-binr.php');
	} else {
		$page->body =  $config->twig->render('warehouse/binr/direction/bin-form.twig', ['page' => $page, 'inputname' => 'frombin']);
		$page->body .= $config->twig->render('warehouse/inventory/bins-modal.twig', ['warehouse' => $warehouse]);

		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		$config->scripts->append(hash_templatefile('scripts/warehouse/shared.js'));
		$config->scripts->append(hash_templatefile('scripts/warehouse/binr-from.js'));

		include __DIR__ . "/basic-page.php";
	}
