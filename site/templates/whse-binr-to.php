<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$tobin = $warehouse->validate_bin($values->text('tobin')) ? $values->text('tobin') : '';
	$config->binr = $modules->get('ConfigsBinr');

	if (!empty($tobin)) {
		$page->headline = "To bin: $tobin";
		include('./whse-binr.php');
	} else {
		$page->body =  $config->twig->render('warehouse/binr/direction/bin-form.twig', ['page' => $page, 'inputname' => 'tobin']);
		$page->body .= $config->twig->render('warehouse/inventory/bins-modal.twig', ['warehouse' => $warehouse, 'config' => $config->binr]);

		$config->scripts->append(Pauldro\ProcessWire\FileHasher::instance()->getHashUrl('scripts/lib/jquery-validate.js'));
		$config->scripts->append(Pauldro\ProcessWire\FileHasher::instance()->getHashUrl('scripts/warehouse/shared.js'));
		$config->scripts->append(Pauldro\ProcessWire\FileHasher::instance()->getHashUrl('scripts/warehouse/binr-to.js'));

		include __DIR__ . "/basic-page.php";
	}
