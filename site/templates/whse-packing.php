<?php
	use ProcessWire\WireHttp;

	$warehousepacking = $modules->get('WarehousePacking');
	$warehousepacking->set_sessionID(session_id());
	$whsesession = $warehousepacking->get_whsesession();
	$warehouse   = $warehousepacking->get_warehouseconfig();
	$http = new WireHttp();

	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');

		if (SalesOrderQuery::create()->orderExists($ordn)) {
			$warehousepacking->set_ordn($ordn);
			$http->get($page->get('template=redir')->url."?action=get-pack-notes&ordn=$ordn");
			$page->body = $config->twig->render('warehouse/packing/order-notes.twig', ['page' => $page]);
		} else {
			$page->body = $config->twig->render('warehouse/packing/status.twig', ['page' => $page, 'message' => "Error finding Sales Order # $ordn"]);
		}
	} else {
		$page->formurl = $page->parent->child('template=redir')->url;
		$page->body = $config->twig->render('warehouse/packing/sales-order-form.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
