<?php
	use Controllers\Wm\Binr;
	if (Binr::validateUserPermission() === false) {
		$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: binr"]);
		include('./basic-page.php');
		return false;
	}

	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$frombin = $warehouse->validate_bin($input->$rm->text('frombin')) ? $input->$rm->text('frombin') : '';
	$config->binr = $modules->get('ConfigsBinr');

	if (!empty($frombin)) {
		$page->title .= " From Bin $frombin";
		include('./whse-binr.php');
	} else {
		$page->body =  $config->twig->render('warehouse/binr/direction/bin-form.twig', ['page' => $page, 'inputname' => 'frombin']);
		$page->body .= $config->twig->render('warehouse/inventory/bins-modal.twig', ['warehouse' => $warehouse, 'config' => $config->binr]);

		$config->scripts->append(Pauldro\ProcessWire\FileHasher::instance()->getHashUrl('scripts/lib/jquery-validate.js'));
		$config->scripts->append(Pauldro\ProcessWire\FileHasher::instance()->getHashUrl('scripts/warehouse/shared.js'));
		$config->scripts->append(Pauldro\ProcessWire\FileHasher::instance()->getHashUrl('scripts/warehouse/binr-from.js'));

		include __DIR__ . "/basic-page.php";
	}
